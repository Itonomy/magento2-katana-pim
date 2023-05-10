<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel\KatanaImport;

use Itonomy\Katanapim\Model\KatanaImport as Model;
use Itonomy\Katanapim\Model\ResourceModel\KatanaImport as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'katanapim_import_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
