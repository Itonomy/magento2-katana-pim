<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Process\Entity;

use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Itonomy\Katanapim\Api\Data\ImportInterface;
use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Api\KatanaImportRepositoryInterface;
use Itonomy\Katanapim\Model\AttributeMappingRepository;
use Itonomy\Katanapim\Model\KatanaImport;
use Itonomy\Katanapim\Model\KatanaImportHelper;
use Itonomy\Katanapim\Model\RestClient;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;

class Specifications implements ImportInterface
{
    private const URL_PART = 'Specifications';

    /**
     * @var string
     */
    private string $entityId = '';

    /**
     * @var string[]
     */
    public array $type = [
        0 => 'select',
        10 => 'text', //more than 255
        20 => 'text'
    ];

    /**
     * @var RestClient
     */
    private RestClient $rest;

    /**
     * @var AttributeMappingRepository
     */
    private AttributeMappingRepository $attributeMappingRepository;

    /**
     * @var ProgressBar|null
     */
    private ?ProgressBar $progressBar = null;

    /**
     * @var KatanaImportInterface
     */
    private KatanaImportInterface $katanaImport;

    /**
     * @var KatanaImportHelper
     */
    private KatanaImportHelper $katanaImportHelper;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var KatanaImportRepositoryInterface
     */
    private KatanaImportRepositoryInterface $katanaImportRepository;

    /**
     * @param RestClient $rest
     * @param AttributeMappingRepository $attributeMappingRepository
     * @param KatanaImportHelper $katanaImportHelper
     * @param KatanaImportRepositoryInterface $katanaImportRepository
     * @param Logger $logger
     */
    public function __construct(
        RestClient $rest,
        AttributeMappingRepository $attributeMappingRepository,
        KatanaImportHelper $katanaImportHelper,
        KatanaImportRepositoryInterface $katanaImportRepository,
        Logger $logger
    ) {
        $this->rest = $rest;
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->katanaImportHelper = $katanaImportHelper;
        $this->logger = $logger;
        $this->katanaImportRepository = $katanaImportRepository;
    }

    /**
     * Execute specifications import
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws RuntimeException
     */
    public function import(): void
    {
        $i = 0;
        $katanaImport = $this->katanaImportHelper->getImport();
        $this->katanaImportRepository->save($katanaImport->setStatus(KatanaImport::STATUS_RUNNING));
        try {
            do {
                $parameters = new Parameters();
                $parameters->set('specificationFilterModel.pageIndex', $i);

                $specifications = $this->rest->execute(self::URL_PART, Request::METHOD_GET, $parameters);

                if (empty($specifications['Items'])) {
                    //phpcs:ignore Generic.Files.LineLength.TooLong
                    throw new RuntimeException(__('Empty response when trying to retrieve specifications from Katana API.'));
                }

                if ($this->progressBar) {
                    //phpcs:ignore Generic.Files.LineLength.TooLong
                    $this->progressBar->setMessage(\date('H:i:s') . ' downloaded ' . $specifications['TotalCount'] . ' Specifications');
                    $this->progressBar->setMaxSteps($specifications['TotalCount']);
                    $this->progressBar->display();
                }

                $this->processSpecifications($specifications);
                $i++;
            } while ($i < $specifications['TotalPages']);
        } catch (\Throwable $e) {
            $this->logger->critical(
                $e->getMessage(),
                ['entity_type' => $this->getEntityType(), 'entity_id' => $this->getEntityId()]
            );
            $this->katanaImportRepository->save(
                $katanaImport->setStatus(KatanaImport::STATUS_ERROR)->setFinishTime(date('Y-m-d H:i:s'))
            );
            throw $e;
        }

        $this->katanaImportRepository->save(
            $katanaImport->setStatus(KatanaImport::STATUS_COMPLETE)->setFinishTime(date('Y-m-d H:i:s'))
        );
    }

    /**
     * @inheirtDoc
     */
    public function getEntityType(): string
    {
        return self::SPECIFICATIONS_IMPORT_JOB_CODE;
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): string
    {
        if (!empty($this->entityId)) {
            return $this->entityId;
        }
        $this->entityId = uniqid(self::SPECIFICATIONS_IMPORT_JOB_CODE . '_');
        return $this->entityId;
    }

    /**
     * Process specification import
     *
     * @param array $specifications
     * @return void
     * @throws CouldNotSaveException
     */
    public function processSpecifications(array $specifications): void
    {
        $attributes = [];

        foreach ($specifications['Items'] as $specification) {
            $attributes[] = [
                AttributeMappingInterface::KATANA_ID => $specification['Id'],
                //phpcs:ignore Generic.Files.LineLength.TooLong
                AttributeMappingInterface::KATANA_ATTRIBUTE_CODE => !empty($specification['Code']) ? $specification['Code'] : $specification['Name'],
                AttributeMappingInterface::KATANA_ATTRIBUTE_NAME => $specification['Name'],
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE => $this->getType($specification['AttributeTypeId']),
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE_ID => $specification['AttributeTypeId'],
            ];

            if ($this->progressBar) {
                //phpcs:ignore Generic.Files.LineLength.TooLong
                $this->progressBar->setMessage(\date('H:i:s') . ' ' . $specification['Name'] . ' processed');
                $this->progressBar->advance(1);
            }
        }

        $this->attributeMappingRepository->insertOnDuplicate(
            $attributes,
            [
                AttributeMappingInterface::KATANA_ATTRIBUTE_CODE,
                AttributeMappingInterface::KATANA_ATTRIBUTE_NAME,
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE,
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE_ID
            ]
        );
    }

    /**
     * Set progress bar
     *
     * @param ProgressBar $progressBar
     * @return Specifications
     */
    public function setProgressBar(ProgressBar $progressBar): Specifications
    {
        $this->progressBar = $progressBar;

        return $this;
    }

    /**
     * Get specification type by id
     *
     * @param mixed $typeId
     * @return string
     */
    public function getType($typeId): string
    {
        if (isset($this->type[$typeId])) {
            return $this->type[$typeId];
        }

        return 'text';
    }
}
