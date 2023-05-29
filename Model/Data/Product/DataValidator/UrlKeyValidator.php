<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataValidator;

use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Model\KatanaImportHelper;
use Magento\Catalog\Model\Product\Url;

class UrlKeyValidator implements ValidatorInterface
{
    /**
     * Url key attribute code
     */
    public const URL_KEY = 'url_key';

    /**
     * Column product sku.
     */
    public const COL_SKU = 'sku';

    /**
     * @var Url
     */
    private Url $productUrl;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var KatanaImportHelper
     */
    private KatanaImportHelper $importHelper;

    /**
     * @param Url $productUrl
     * @param Logger $logger
     * @param KatanaImportHelper $importHelper
     */
    public function __construct(
        Url $productUrl,
        Logger $logger,
        KatanaImportHelper $importHelper
    ) {
        $this->productUrl = $productUrl;
        $this->logger = $logger;
        $this->importHelper = $importHelper;
    }

    /**
     * Check if url_key exists from API or generate one from SKU, if still not available skip the row
     *
     * @param array $productData
     * @return bool
     */
    public function validate(array &$productData): bool
    {
        $urlKey = null;
        if (array_key_exists(self::URL_KEY, $productData)) {
            $urlKey = $this->getUrlKey($productData);
        }

        if ($urlKey) {
            $productData[self::URL_KEY] = $urlKey;
        } else {
            if (!empty($productData[self::COL_SKU])) {
                $this->logger->info(
                    'Skipping product, url_key doesn\'t exist for product with SKU: ',
                    [
                        $productData[self::COL_SKU],
                        'entity_id' => $this->importHelper->getImport()->getEntityId(),
                        'entity_type' => $this->importHelper->getImport()->getEntityType()
                    ]
                );
            } else {
                $this->logger->info(
                    'Skipping product, url_key doesn\'t exist for product',
                    [
                        $productData[self::COL_SKU],
                        'entity_id' => $this->importHelper->getImport()->getEntityId(),
                        'entity_type' => $this->importHelper->getImport()->getEntityType()
                    ]
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Retrieve url key from provided product data.
     *
     * @param array $productData
     * @return string
     */
    protected function getUrlKey(array $productData): string
    {
        if (!empty($productData[self::URL_KEY])) {
            $urlKey = (string) $productData[self::URL_KEY];
            return $this->productUrl->formatUrlKey($urlKey);
        }

        if (!empty($productData[self::COL_SKU])) {
            return $this->productUrl->formatUrlKey($productData[self::COL_SKU]);
        }

        return '';
    }
}
