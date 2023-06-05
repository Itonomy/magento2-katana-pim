<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\AttributeMappingRepository;
use Itonomy\Katanapim\Model\RestClient;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;

class Specifications implements ImportRunnerInterface
{
    private const URL_PART = 'Specifications';

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
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param RestClient $rest
     * @param AttributeMappingRepository $attributeMappingRepository
     * @param Logger $logger
     */
    public function __construct(
        RestClient $rest,
        AttributeMappingRepository $attributeMappingRepository,
        Logger $logger
    ) {
        $this->rest = $rest;
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->logger = $logger;
    }

    /**
     * Execute specifications import
     *
     * @param KatanaImportInterface $importData
     * @return void
     * @throws CouldNotSaveException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function execute(KatanaImportInterface $importData): void
    {
        $i = 0;
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
                ['entity_type' => $importData->getEntityType(), 'entity_id' => $importData->getImportId()]
            );
            throw $e;
        }
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
