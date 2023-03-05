<?php

namespace Itonomy\Katanapim\Model\Storage;

use Magento\Framework\DB\Select;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class DbStorage extends \Magento\UrlRewrite\Model\Storage\DbStorage
{
    /**
     * @inheritDoc
     */
    protected function doReplace(array $urls): array
    {
        $this->connection->beginTransaction();

        try {
            $this->deleteOldUrls($urls);

            $data = [];
            foreach ($urls as $url) {
                $data[] = $url->toArray();
            }

            $data = $this->checkRewriteExists($data);

            $this->insertMultiple($data);

            $this->connection->commit();
            // @codingStandardsIgnoreStart
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            // @codingStandardsIgnoreEnd
            $this->connection->rollBack();

            /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urlConflicted */
            $urlConflicted = [];
            foreach ($urls as $url) {
                $urlFound = $this->doFindOneByData(
                    [
                        UrlRewrite::REQUEST_PATH => $url->getRequestPath(),
                        UrlRewrite::STORE_ID => $url->getStoreId(),
                    ]
                );
                if (isset($urlFound[UrlRewrite::URL_REWRITE_ID])) {
                    $urlConflicted[$urlFound[UrlRewrite::URL_REWRITE_ID]] = $url->toArray();
                }
            }
            if ($urlConflicted) {
                throw new \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException(
                    __('URL key for specified store already exists.'),
                    $e,
                    $e->getCode(),
                    $urlConflicted
                );
            } else {
                throw $e->getPrevious() ?: $e;
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return $urls;
    }

    /**
     * Delete old URLs from DB.
     *
     * @param  UrlRewrite[] $urls
     * @return void
     */
    private function deleteOldUrls(array $urls): void
    {
        $oldUrlsSelect = $this->connection->select();
        $oldUrlsSelect->from(
            $this->resource->getTableName(self::TABLE_NAME)
        );

        $uniqueEntities = $this->prepareUniqueEntities($urls);
        foreach ($uniqueEntities as $storeId => $entityTypes) {
            foreach ($entityTypes as $entityType => $entities) {
                $oldUrlsSelect->orWhere(
                    $this->connection->quoteIdentifier(
                        UrlRewrite::STORE_ID
                    ) . ' = ' . $this->connection->quote($storeId, 'INTEGER') .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_ID
                    ) . ' IN (' . $this->connection->quote($entities, 'INTEGER') . ')' .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_TYPE
                    ) . ' = ' . $this->connection->quote($entityType)
                );
            }
        }

        // prevent query locking in a case when nothing to delete
        $checkOldUrlsSelect = clone $oldUrlsSelect;
        $checkOldUrlsSelect->reset(Select::COLUMNS);
        $checkOldUrlsSelect->columns('count(*)');
        $hasOldUrls = (bool)$this->connection->fetchOne($checkOldUrlsSelect);

        if ($hasOldUrls) {
            $this->connection->query(
                $oldUrlsSelect->deleteFromSelect(
                    $this->resource->getTableName(self::TABLE_NAME)
                )
            );
        }
    }

    /**
     * Prepare array with unique entities
     *
     * @param  UrlRewrite[] $urls
     * @return array
     */
    private function prepareUniqueEntities(array $urls): array
    {
        $uniqueEntities = [];
        /** @var UrlRewrite $url */
        foreach ($urls as $url) {
            $entityIds = (!empty($uniqueEntities[$url->getStoreId()][$url->getEntityType()])) ?
                $uniqueEntities[$url->getStoreId()][$url->getEntityType()] : [];

            if (!\in_array($url->getEntityId(), $entityIds)) {
                $entityIds[] = $url->getEntityId();
            }
            $uniqueEntities[$url->getStoreId()][$url->getEntityType()] = $entityIds;
        }

        return $uniqueEntities;
    }

    /**
     * Check if we have a duplicate (maybe exists product with same name => same Url Rewrite)
     * if exists then add additional index to avoid a duplicates
     *
     * @param array $rewrites
     * @return array
     */
    private function checkRewriteExists(array $rewrites): array
    {
        $index = 0;
        foreach ($rewrites as &$rewrite) {
            // check if same Url Rewrite already exists
            $originalRequestPath = trim($rewrite['request_path']);

            // skip empty Url Rewrites - I don't know how this possible, but it happens in Magento:
            // maybe someone did import product programmatically and product(s) name(s) are empty
            if (empty($originalRequestPath)) {
                continue;
            }
            // split generated Url Rewrite into parts
            $pathParts = pathinfo($originalRequestPath);

            // remove leading/trailing slashes and dots from parts
            $pathParts['dirname'] = trim($pathParts['dirname'], './');
            $pathParts['filename'] = trim($pathParts['filename'], './');

            // If the last symbol was slash - let's use it as url suffix
            $urlSuffix = substr($originalRequestPath, -1) === '/' ? '/' : '';

            // re-set Url Rewrite with sanitized parts
            $rewrite['request_path'] = $this->_mergePartsIntoRewriteRequest($pathParts, '', $urlSuffix);

            while ($this->_urlRewriteExists($rewrite)) {
                $index++;
                $rewrite['request_path'] = $this->_mergePartsIntoRewriteRequest($pathParts, (string)$index, $urlSuffix);
            }
        }

        return $rewrites;
    }

    /**
     * Check if Url Rewrite with same request path exists
     * @param array $rewrite
     * @return string
     */
    protected function _urlRewriteExists(array $rewrite): string
    {
        $select = $this->connection->select()
            ->from($this->resource->getTableName(self::TABLE_NAME), ['url_rewrite_id'])
            ->where('entity_type = ?', $rewrite['entity_type'])
            ->where('request_path = ?', $rewrite['request_path'])
            ->where('store_id = ?', $rewrite['store_id'])
            ->where('entity_id != ?', $rewrite['entity_id']);
        return $this->connection->fetchOne($select);
    }

    /**
     * Merge Url Rewrite parts into one string
     * @param $pathParts
     * @param string $index
     * @param string $urlSuffix
     * @return string
     */
    protected function _mergePartsIntoRewriteRequest($pathParts, string $index = '', string $urlSuffix = ''): string
    {
        return (!empty($pathParts['dirname']) ? $pathParts['dirname'] . '/' : '') . $pathParts['filename']
            . (!empty($index) ? '-' . $index : '')
            . (!empty($pathParts['extension']) ? '.' . $pathParts['extension'] : '')
            . ($urlSuffix ?: '');
    }
}
