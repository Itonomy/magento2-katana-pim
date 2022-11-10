<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;

class Getfields extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Itonomy_Katanapim::attributes';

    /**
     * @InheritDoc
     */
    public function execute()
    {
        return $this->_redirect('*/*/index', ['store' => 0]);
    }
}
