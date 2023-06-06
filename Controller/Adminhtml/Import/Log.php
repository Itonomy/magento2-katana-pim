<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Controller\Adminhtml\Import;

use Itonomy\Katanapim\Block\Adminhtml\LogListing;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class Log extends Action
{
    /**
     * @var RawFactory
     */
    public RawFactory $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    public LayoutFactory $layoutFactory;

    /**
     * Log constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;

        parent::__construct($context);
    }

    /**
     * View action
     *
     * @return Raw
     */
    public function execute(): Raw
    {
        $content = $this->layoutFactory->create()
            ->createBlock(
                LogListing::class
            );

        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($content->toHtml());
    }
}
