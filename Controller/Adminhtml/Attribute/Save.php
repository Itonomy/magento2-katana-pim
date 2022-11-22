<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Itonomy\Katanapim\Model\AttributeMappingFactory;
use Itonomy\Katanapim\Model\AttributeMappingRepository;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Itonomy_Katanapim::attributes';

    /**
     * @var AttributeMappingFactory
     */
    private AttributeMappingFactory $attributeMapping;

    /**
     * @var AttributeMappingRepository
     */
    private AttributeMappingRepository $attributeMappingRepository;
    private LoggerInterface $logger;

    /**
     * @param Context $context
     * @param AttributeMappingFactory $attributeMapping
     * @param AttributeMappingRepository $attributeMappingRepository
     */
    public function __construct(
        Context $context,
        AttributeMappingFactory $attributeMapping,
        AttributeMappingRepository $attributeMappingRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->attributeMapping = $attributeMapping;
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->logger = $logger;
    }

    /**
     * @InheritDoc
     */
    public function execute()
    {
        $this->logger->info(json_encode($this->getRequest()->getParams()));
        try {
            $data = $this->getRequest()->getParam('itonomy_katanapim_dynamic_rows_container');

            if (!is_array($data) || empty($data)) {
                $this->messageManager->addErrorMessage(__('No data to save'));
                return $this->_redirect('*/*/index', ['store' => 0]);
            }

            $idsForSave = [];

            foreach ($data as $itemData) {
                if (empty($itemData['id'])) {
                    continue;
                }
                $idsForSave[] = $itemData['id'];
            }

            $this->attributeMappingRepository->deleteMapping($idsForSave);

            foreach ($data as $itemData) {
                if (empty($itemData['id'])) {
                    unset($itemData['id']);
                }

                $itemData['is_configurable'] = (int) ($itemData['is_configurable'] === 'true');

                /** @var \Itonomy\Katanapim\Model\AttributeMapping $model */
                $model = $this->attributeMapping->create();
                $model->addData($itemData);

                $this->attributeMappingRepository->save($model);
            }

            $this->messageManager->addSuccessMessage(__('Attribute Mapping has been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $this->_redirect('*/*/index', ['store' => 0]);
    }
}
