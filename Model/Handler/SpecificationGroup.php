<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Api\Data\AttributeSetInterface;
use Itonomy\Katanapim\Api\Data\AttributeSetToAttributeInterface;
use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\AttributeSetRepository;
use Itonomy\Katanapim\Model\AttributeSetToAttributeRepository;
use Itonomy\Katanapim\Model\RestClient;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class SpecificationGroup implements ImportRunnerInterface
{
    private const URL_PART = 'Spec/SpecificationGroup';

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
     * @var OutputInterface|null
     */
    private ?OutputInterface $cliOutput;

    /**
     * @param RestClient $rest
     * @param AttributeSetRepository $attributeSetRepository
     * @param AttributeSetToAttributeRepository $attributeSetToAttributeRepository
     * @param Logger $logger
     */
    public function __construct(
        RestClient $rest,
        AttributeSetRepository $attributeSetRepository,
        AttributeSetToAttributeRepository $attributeSetToAttributeRepository,
        Logger $logger,
    ) {
        $this->rest = $rest;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeSetToAttributeRepository = $attributeSetToAttributeRepository;
        $this->logger = $logger;
        $this->cliOutput = null;
    }

    /**
     * Execute specification group import
     *
     * @param KatanaImportInterface $importData
     * @return void
     */
    public function execute(KatanaImportInterface $importData): void
    {
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
            $this->logger->error(
                $e->getMessage(),
                ['entity_type' => $importData->getImportType(), 'entity_id' => $importData->getImportId()]
            );
            return;
        }
    }

    /**
     * Set the cli output
     *
     * @param OutputInterface $cliOutput
     * @return void
     */
    public function setCliOutput(OutputInterface $cliOutput): void
    {
        $this->cliOutput = $cliOutput;
    }
}
