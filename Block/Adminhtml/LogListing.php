<?php

namespace Itonomy\Katanapim\Block\Adminhtml;

use Itonomy\DatabaseLogger\Api\EntityLogRepositoryInterface;
use Itonomy\Katanapim\Api\KatanaImportRepositoryInterface;
use Magento\Backend\Block\Template;


class LogListing extends Template
{
    /**
     * @var string
     */
    public $_template = 'Itonomy_Katanapim::log_listing.phtml';

    /**
     * @var EntityLogRepositoryInterface
     */
    private EntityLogRepositoryInterface $entityLogRepository;

    /**
     * @var KatanaImportRepositoryInterface
     */
    private KatanaImportRepositoryInterface $katanaImportRepository;

    /**
     * @param Template\Context $context
     * @param EntityLogRepositoryInterface $entityLogRepository
     * @param KatanaImportRepositoryInterface $katanaImportRepository
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        EntityLogRepositoryInterface $entityLogRepository,
        KatanaImportRepositoryInterface $katanaImportRepository
    ) {
        $this->entityLogRepository = $entityLogRepository;
        $this->katanaImportRepository = $katanaImportRepository;
        parent::__construct($context);
    }

    /**
     * Get import log listing
     * @return array
     */
    public function getLogListing(): array
    {
        $id = $this->getRequest()->getParam('id');
        $katanaImport = $this->katanaImportRepository->getById($id);
        $data = $this->entityLogRepository->getByLogEntityId($katanaImport->getImportId());
        return $data->getData();
    }
}
