<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel;

use Itonomy\Katanapim\Api\Data\AttributeSetInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AttributeSet extends AbstractDb
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('katanapim_attribute_set', 'id');
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
}
