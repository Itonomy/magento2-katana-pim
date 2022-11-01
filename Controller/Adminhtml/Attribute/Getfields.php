<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Getfields extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Itonomy_Katanapim::attributes';

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @InheritDoc
     */
    public function execute()
    {
        return $this->_redirect('*/*/index', ['store' => 0]);
    }
}
