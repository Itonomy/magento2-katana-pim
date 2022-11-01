<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel;

use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AttributeMapping extends AbstractDb
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('katanapim_attribute_mapping', 'id');
    }

    /**
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = []): int
    {
        return $this->getConnection()
            ->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * @param array $savedIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteMapping(array $savedIds = []): AttributeMapping
    {
        if (empty($savedIds)) {
            $this->getConnection()
                ->truncateTable($this->getMainTable());
        } else {
            $this->getConnection()
                ->delete(
                    $this->getMainTable(),
                    ['id NOT IN (?)' => $savedIds]
                );
        }

        return $this;
    }
}
