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
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class for translating specifications ("attributes" in magento) and adding and translating their options.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecificationsLocalization implements ImportRunnerInterface
{
    private const URL_PART = 'Specifications';

    public const IMPORT_ERROR = 'error';
    public const IMPORT_INFO = 'info';

    /**
     * @var RestClient
     */
    private RestClient $rest;

    /**
     * @var array
     */
    private array $attributeMapping;

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
     * @var OutputInterface|null
     */
    private ?OutputInterface $cliOutput;

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
        $this->cliOutput = null;
    }

    /**
     * Execute specifications localization import
     *
     * @param KatanaImportInterface $importInfo
     * @return void
     * @throws RuntimeException
     */
    public function execute(KatanaImportInterface $importInfo): void
    {
        $page = 0;

        try {
            do {
                $parameters = new Parameters();
                $parameters->set('specificationFilterModel.pageIndex', $page);
                $apiData = $this->rest->execute(self::URL_PART, Request::METHOD_GET, $parameters);

                if (empty($apiData['Items'])) {
                    throw new RuntimeException(__('Specification data empty.'));
                }

                $items = $this->reindexApiSpecs($apiData['Items']);
                $this->importItems($items, $page, $importInfo);

                $page++;
            } while ($page < $apiData['TotalPages']);
        } catch (Throwable $e) {
            $this->log(
                $e->getMessage(),
                $importInfo,
                self::IMPORT_ERROR
            );

            throw new RuntimeException(__(
                'Error while trying to import specifications translations. ' . $e->getMessage()
            ));
        }

        $this->log(
            'Specification localization import finished',
            $importInfo
        );
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

    /**
     * Process specifications localization
     *
     * @param array $items
     * @param int $page
     * @param KatanaImportInterface $importInfo
     * @throws NoSuchEntityException
     * @throws RuntimeException
     */
    public function importItems(array $items, int $page, KatanaImportInterface $importInfo): void
    {
        $this->log(PHP_EOL . 'Processing page ' . $page, $importInfo);

        $existingMappedSpecs = $this->getSpecificationsMapping();

        foreach ($items as $apiSpecification) {
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

    /**
     * Log some information to the available output streams
     *
     * TODO: Move Output Stream handler / Logger outside this class.
     *
     * @param string $string
     * @param KatanaImportInterface $importInfo
     * @param string $level
     * @return void
     */
    private function log(string $string, KatanaImportInterface $importInfo, string $level = self::IMPORT_INFO): void
    {
        if ($level === self::IMPORT_ERROR) {
            if ($this->cliOutput instanceof OutputInterface) {
                $this->cliOutput->writeln('<error>' . $string . '</error>');
            }

            $this->logger->error($string, ['entity_type' => $importInfo->getImportType(), 'entity_id' => $importInfo->getImportId()]);
        } else {
            if ($this->cliOutput instanceof OutputInterface) {
                $this->cliOutput->writeln('<info>' . $string . '</info>');
            }

            $this->logger->info($string, ['entity_type' => $importInfo->getImportType(), 'entity_id' => $importInfo->getImportId()]);
        }
    }
}
