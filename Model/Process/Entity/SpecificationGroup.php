<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Process\Entity;

use Itonomy\Katanapim\Api\Data\AttributeSetInterface;
use Itonomy\Katanapim\Api\Data\AttributeSetToAttributeInterface;
use Itonomy\Katanapim\Api\Data\ImportInterface;
use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Api\KatanaImportRepositoryInterface;
use Itonomy\Katanapim\Model\AttributeSetRepository;
use Itonomy\Katanapim\Model\AttributeSetToAttributeRepository;
use Itonomy\Katanapim\Model\KatanaImport;
use Itonomy\Katanapim\Model\KatanaImportHelper;
use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Model\RestClient;
use Magento\Framework\Exception\RuntimeException;

class SpecificationGroup implements ImportInterface
{
    private const URL_PART = 'Spec/SpecificationGroup';

    /**
     * @var string
     */
    private string $entityId = '';

    /**
     * @var RestClient
     */
    private RestClient $rest;

    /**
     * @var AttributeSetRepository
     */
    private AttributeSetRepository $attributeSetRepository;

    /**
     * @var AttributeSetToAttributeRepository
     */
    private AttributeSetToAttributeRepository $attributeSetToAttributeRepository;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var KatanaImportHelper
     */
    private KatanaImportHelper $katanaImportHelper;

    /**
     * @var KatanaImportRepositoryInterface
     */
    private KatanaImportRepositoryInterface $katanaImportRepository;

    /**
     * @param RestClient $rest
     * @param AttributeSetRepository $attributeSetRepository
     * @param AttributeSetToAttributeRepository $attributeSetToAttributeRepository
     * @param Logger $logger
     * @param KatanaImportHelper $katanaImportHelper
     * @param KatanaImportRepositoryInterface $katanaImportRepository
     */
    public function __construct(
        RestClient $rest,
        AttributeSetRepository $attributeSetRepository,
        AttributeSetToAttributeRepository $attributeSetToAttributeRepository,
        Logger $logger,
        KatanaImportHelper $katanaImportHelper,
        KatanaImportRepositoryInterface $katanaImportRepository
    ) {
        $this->rest = $rest;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeSetToAttributeRepository = $attributeSetToAttributeRepository;
        $this->logger = $logger;
        $this->katanaImportHelper = $katanaImportHelper;
        $this->katanaImportRepository = $katanaImportRepository;
    }

    /**
     * Execute specification group import
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function import(): void
    {
        $katanaImport = $this->katanaImportHelper->getImport();
        $this->katanaImportRepository->save($katanaImport->setStatus(KatanaImport::STATUS_RUNNING));

        try {
            $specificationGroups = $this->rest->execute(self::URL_PART);

            if (empty($specificationGroups)) {
                throw new RuntimeException(__('Empty specification groups recieved from Katana API.'));
            }

            $groups = $setToAttribute = [];

            foreach ($specificationGroups as $group) {
                $groups[] = [
                    AttributeSetInterface::ID => $group['Id'],
                    AttributeSetInterface::NAME => $group['Name'],
                    AttributeSetInterface::CODE => $group['Code'],
                ];

                if (empty($group['Specifications'])) {
                    continue;
                }

                foreach ($group['Specifications'] as $specification) {
                    $setToAttribute[] = [
                        AttributeSetToAttributeInterface::SET_ID => $group['Id'],
                        AttributeSetToAttributeInterface::ATTRIBUTE_ID => $specification['Id']
                    ];
                }
            }

            $this->attributeSetRepository->insertOnDuplicate(
                $groups,
                [
                    AttributeSetInterface::ID,
                    AttributeSetInterface::NAME,
                    AttributeSetInterface::CODE
                ]
            );

            $this->attributeSetToAttributeRepository->insertOnDuplicate(
                $setToAttribute,
                [
                    AttributeSetToAttributeInterface::SET_ID,
                    AttributeSetToAttributeInterface::ATTRIBUTE_ID
                ]
            );
        } catch (\Throwable $e) {
            $this->katanaImportRepository->save(
                $katanaImport->setStatus(KatanaImport::STATUS_ERROR)->setFinishTime(date('Y-m-d H:i:s'))
            );
            $this->logger->error(
                $e->getMessage(),
                ['entity_type' => $this->getEntityType(), 'entity_id' => $this->getEntityId()]
            );
            return;
        }

        $this->katanaImportRepository->save(
            $katanaImport->setStatus(KatanaImport::STATUS_COMPLETE)->setFinishTime(date('Y-m-d H:i:s'))
        );
    }

    /**
     * @inheritDoc
     */
    public function getEntityType(): string
    {
        return self::SPECIFICATIONS_GROUP_IMPORT_JOB_CODE;
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): string
    {
        if (!empty($this->entityId)) {
            return $this->entityId;
        }
        $this->entityId = uniqid(self::SPECIFICATIONS_GROUP_IMPORT_JOB_CODE . '_');
        return $this->entityId;
    }
}
