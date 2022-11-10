<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AttributeSetToAttribute extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('katanapim_set_to_attribute', 'id');
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     * @throws LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = []): int
    {
        return $this->getConnection()
            ->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * Get configurable product variation codes by specification group id
     *
     * @param int $specificationGroupId
     * @return array
     * @throws LocalizedException
     */
    public function getConfigurableVariationsCodes(int $specificationGroupId): array
    {
        $katanaPimAttributeMapping = $this->getConnection()->getTableName('katanapim_attribute_mapping');

        $select = $this->getConnection()->select()
            ->from(['main' => $this->getMainTable()], [])
            ->join(
                ['kam' => $katanaPimAttributeMapping],
                'main.attribute_id = kam.katana_id',
                ['katana_attribute_code', 'magento_attribute_code']
            )
            ->where('main.set_id = ?', $specificationGroupId)
            ->where('kam.is_configurable = ?', 1)
            ->where('kam.magento_attribute_code IS NOT NULL');

        return $this->getConnection()->fetchPairs($select);
    }
}
