<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel\AttributeMapping;

use Itonomy\Katanapim\Model\AttributeMapping as AttributeMappingModel;
use Itonomy\Katanapim\Model\ResourceModel\AttributeMapping as AttributeMappingResourceModel;
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
        $this->_init(AttributeMappingModel::class, AttributeMappingResourceModel::class);
    }
}
