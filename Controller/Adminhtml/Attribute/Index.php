<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Itonomy_Katanapim::attributes';

    protected PageFactory $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $store = $this->getRequest()->getParam('store', null);

        if ($store === null) {
            $this->_redirect('*/*/index', ['store' => 0]);
            return;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Itonomy_Katanapim::base');
        $resultPage->getConfig()->getTitle()->prepend(__('Katana PIM Attributes Mapping'));

        return $resultPage;
    }
}
