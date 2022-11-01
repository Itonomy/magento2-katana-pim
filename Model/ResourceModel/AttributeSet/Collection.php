<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel\AttributeSet;

use Itonomy\Katanapim\Model\AttributeSet as AttributeSetModel;
use Itonomy\Katanapim\Model\ResourceModel\AttributeSet as AttributeSetResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AttributeSetModel::class, AttributeSetResourceModel::class);
    }
}
