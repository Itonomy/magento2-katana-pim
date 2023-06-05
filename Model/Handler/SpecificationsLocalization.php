<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Process\SpecificationsLocalization\SpecificationOptions;
use Itonomy\Katanapim\Model\Process\SpecificationsLocalization\SpecificationTranslation;
use Itonomy\Katanapim\Model\ResourceModel\AttributeMapping\CollectionFactory;
use Itonomy\Katanapim\Model\RestClient;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;

/**
 * Class for translating specifications ("attributes" in magento) and adding and translating their options.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecificationsLocalization implements ImportRunnerInterface
{
    private const URL_PART = 'Specifications';

    /**
     * @var RestClient
     */
    private RestClient $rest;

    /**
     * @var array
     */
    private array $attributeMapping;

    /**
     * @var ProgressBar|null
     */
    private ?ProgressBar $progressBar = null;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $mappingCollectionFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private ProductAttributeRepositoryInterface $attributeRepository;

    /**
     * @var SpecificationTranslation
     */
    private SpecificationTranslation $specificationTranslationProcessor;

    /**
     * @var SpecificationOptions
     */
    private SpecificationOptions $specificationOptionsProcessor;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * SpecificationsLocalization constructor.
     *
     * @param RestClient $rest
     * @param CollectionFactory $mappingCollectionFactory
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SpecificationTranslation $specificationTranslationProcessor
     * @param SpecificationOptions $specificationOptionsProcessor
     * @param Logger $logger
     */
    public function __construct(
        RestClient $rest,
        CollectionFactory $mappingCollectionFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        SpecificationTranslation $specificationTranslationProcessor,
        SpecificationOptions $specificationOptionsProcessor,
        Logger $logger
    ) {
        $this->rest = $rest;
        $this->attributeMapping = [];
        $this->mappingCollectionFactory = $mappingCollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->specificationTranslationProcessor = $specificationTranslationProcessor;
        $this->specificationOptionsProcessor = $specificationOptionsProcessor;
        $this->logger = $logger;
    }

    /**
     * Execute specifications localization import
     *
     * @param KatanaImportInterface $importData
     * @return void
     * @throws RuntimeException
     */
    public function execute(KatanaImportInterface $importData): void
    {
        $i = 0;

        try {
            do {
                $parameters = new Parameters();
                $parameters->set('specificationFilterModel.pageIndex', $i);

                $apiData = $this->rest->execute(self::URL_PART, Request::METHOD_GET, $parameters);

                if (empty($apiData['Items'])) {
                    throw new RuntimeException(__('Specification data empty.'));
                }

                if ($this->progressBar) {
                    //phpcs:ignore Generic.Files.LineLength.TooLong
                    $this->progressBar->setMessage(\date('H:i:s') . ' downloaded ' . $apiData['TotalCount'] . ' Specifications');
                    $this->progressBar->setMaxSteps($apiData['TotalCount']);
                    $this->progressBar->display();
                }

                $apiSpecifications = $this->reindexApiSpecs($apiData['Items']);

                try {
                    $this->processSpecificationLocalization($apiSpecifications);
                } catch (Throwable $e) {
                    throw new RuntimeException(__(
                        'Error while trying to import specifications translations. ' . $e->getMessage()
                    ));
                }

                $i++;
            } while ($i < $apiData['TotalPages']);
        } catch (Throwable $e) {
            $this->logger->error(
                $e->getMessage(),
                ['entity_type' => $importData->getEntityType(), 'entity_id' => $importData->getImportId()]
            );
            throw new RuntimeException(__(
                'Error while trying to import specifications translations. ' . $e->getMessage()
            ));
        }
    }

    /**
     * Process specifications localization
     *
     * @param array $apiSpecifications
     * @throws NoSuchEntityException
     * @throws RuntimeException
     */
    public function processSpecificationLocalization(array $apiSpecifications): void
    {
        $existingMappedSpecs = $this->getSpecificationsMapping();

        foreach ($apiSpecifications as $apiSpecification) {
            $id = $apiSpecification['Id'];
            $mappedSpecification = $existingMappedSpecs[$id] ?? null;

            if ($mappedSpecification === null) {
                continue;
            }

            $attributeCode = $mappedSpecification[AttributeMappingInterface::MAGENTO_ATTRIBUTE_CODE];

            $productAttribute = $this->attributeRepository->get($attributeCode);

            if (!$productAttribute instanceof ProductAttributeInterface) {
                throw new RuntimeException(__(
                    'Error while trying to process specifications localization. Could not retrieve attribute. '
                    . $attributeCode
                ));
            }

            $localizationData = $apiSpecification['LocalizedProperties'] ?? null;

            if (!empty($localizationData)) {
                try {
                    $this->specificationTranslationProcessor->process($localizationData, $productAttribute);
                } catch (Throwable $e) {
                    throw new RuntimeException(__(
                        'Error while trying to process specifications translation. ' . $e->getMessage()
                    ));
                }
            }

            if (in_array($productAttribute->getFrontendInput(), ['select', 'multiselect'])) {
                $optionsData = $apiSpecification['Options'] ?? null;
                if (!empty($optionsData)) {
                    try {
                        $this->specificationOptionsProcessor->process($optionsData, $productAttribute);
                    } catch (Throwable $e) {
                        throw new RuntimeException(__(
                            'Error while trying to process specifications options. ' . $e->getMessage()
                        ));
                    }
                }
            }
        }
    }

    /**
     * Set progress bar
     *
     * @param ProgressBar $progressBar
     * @return SpecificationsLocalization
     */
    public function setProgressBar(ProgressBar $progressBar): SpecificationsLocalization
    {
        $this->progressBar = $progressBar;

        return $this;
    }

    /**
     * Get specifications that have a magento attribute code assigned to them
     *
     * @return array
     */
    private function getSpecificationsMapping(): array
    {
        if (empty($this->attributeMapping)) {
            $collection = $this->mappingCollectionFactory->create();
            $collection->addFieldToFilter(AttributeMappingInterface::MAGENTO_ATTRIBUTE_CODE, ['notnull' => true]);
            $collection->addFieldToFilter(AttributeMappingInterface::MAGENTO_ATTRIBUTE_CODE, ['neq' => '']);
            $items = $collection->toArray()['items'] ?? [];

            foreach ($items as $item) {
                $this->attributeMapping[$item[AttributeMappingInterface::KATANA_ID]] = $item;
            }
        }

        return $this->attributeMapping;
    }

    /**
     * Reindex the api specifications array using the api specification id as key
     *
     * @param array|null $apiSpecs
     * @return array
     */
    private function reindexApiSpecs(?array $apiSpecs): array
    {
        $output = [];

        foreach ($apiSpecs as $item) {
            $output[$item['Id']] = $item;
        }

        return $output;
    }
}
