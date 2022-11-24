<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Controller\Adminhtml\Imports;

use Itonomy\Katanapim\Cron\SpecificationsLocalizationImport;
use Itonomy\Katanapim\Model\Cron\ScheduleHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class ImportSpecificationTranslations extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'Itonomy_Katanapim::import_specification_translations';

    /**
     * @var ScheduleHelper
     */
    private ScheduleHelper $scheduleHelper;

    /**
     * @param Context $context
     * @param ScheduleHelper $scheduleHelper
     */
    public function __construct(
        Context $context,
        ScheduleHelper $scheduleHelper
    ) {
        parent::__construct($context);
        $this->scheduleHelper = $scheduleHelper;
    }

    /**
     * Flush cache storage
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        try {
            $this->scheduleHelper->scheduleCronjob(SpecificationsLocalizationImport::JOB_CODE);
            $this->messageManager->addSuccessMessage(__(
                "Product specification translation and options import scheduled. It should start shortly"
            ));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('katanapim/imports/index');
        return $resultRedirect;
    }
}
