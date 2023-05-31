<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class KatanaImport extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'katanapim_import_resource_model';

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('katanapim_import', 'id');
        $this->_useIsObjectNew = true;
    }
}
