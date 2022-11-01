<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel\AttributeSetToAttribute;

use Itonomy\Katanapim\Model\AttributeSetToAttribute as ASTAModel;
use Itonomy\Katanapim\Model\ResourceModel\AttributeSetToAttribute as ASTAResourceModel;
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
        $this->_init(ASTAModel::class, ASTAResourceModel::class);
    }
}
