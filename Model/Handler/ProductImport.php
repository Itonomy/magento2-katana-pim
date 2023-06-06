<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Config\Katana;
use Itonomy\Katanapim\Model\Data\Product\DataParser;
use Itonomy\Katanapim\Model\Data\Product\DataPreprocessor;
use Itonomy\Katanapim\Model\Data\Product\DataValidator;
use Itonomy\Katanapim\Model\Data\Product\LocalizedDataParser;
use Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResult;
use Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResult\Error;
use Itonomy\Katanapim\Model\Import\Product\PersistenceProcessorInterface;
use Itonomy\Katanapim\Model\RestClient;
use Itonomy\Katanapim\Setup\Patch\Data\AddKatanaPimProductIdAttribute;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImport implements ImportRunnerInterface
{
    private const URL_PART = 'Product';
    private const REQUEST_PAGE_INDEX_KEY = 'filterModel.paging.pageIndex';
    private const REQUEST_PAGE_SIZE_KEY = 'filterModel.paging.pageSize';

    public const IMPORT_ERROR = 'error';
    public const IMPORT_INFO = 'info';

    /**
     * @var RestClient
     */
    private RestClient $restClient;

    /**
     * @var DataParser
     */
    private DataParser $dataParser;

    /**
     * @var PersistenceProcessorInterface
     */
    private PersistenceProcessorInterface $persistenceProcessor;

    /**
     * @var DataPreprocessor
     */
    private DataPreprocessor $dataPreprocessor;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var LocalizedDataParser
     */
    private LocalizedDataParser $localizedDataParser;

    /**
     * @var Katana
     */
    private Katana $katanaConfig;

    /**
     * @var OutputInterface|null
     */
    private ?OutputInterface $cliOutput;

    /**
     * @var DataValidator
     */
    private DataValidator $dataValidator;

    /**
     * ProductImport constructor.
     *
     * @param RestClient $restClient
     * @param DataParser $dataParser
     * @param LocalizedDataParser $localizedDataParser
     * @param DataPreprocessor $dataPreprocessor
     * @param DataValidator $dataValidator
     * @param PersistenceProcessorInterface $persistenceProcessor
     * @param ManagerInterface $eventManager
     * @param Logger $logger
     * @param Katana $katanaConfig
     */
    public function __construct(
        RestClient $restClient,
        DataParser $dataParser,
        LocalizedDataParser $localizedDataParser,
        DataPreprocessor $dataPreprocessor,
        DataValidator $dataValidator,
        PersistenceProcessorInterface $persistenceProcessor,
        ManagerInterface $eventManager,
        Logger $logger,
        Katana $katanaConfig
    ) {
        $this->restClient = $restClient;
        $this->dataParser = $dataParser;
        $this->localizedDataParser = $localizedDataParser;
        $this->dataPreprocessor = $dataPreprocessor;
        $this->persistenceProcessor = $persistenceProcessor;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->katanaConfig = $katanaConfig;
        $this->cliOutput = null;
        $this->dataValidator = $dataValidator;
    }

    /**
     * Import products from Katana PIM
     *
     * @param KatanaImportInterface $importInfo
     * @return void
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function execute(KatanaImportInterface $importInfo): void
    {
        $page = 0;
        $parameters = $this->prepareRequestArray();

        try {
            do {
                $parameters->set(self::REQUEST_PAGE_INDEX_KEY, $page++);

                $response = $this->restClient->execute(
                    self::URL_PART,
                    Request::METHOD_GET,
                    $parameters
                );

                $items = $response['Items'] ?? [];

                if (empty($items)) {
                    break;
                }

                $this->importItems($items, $page, $importInfo);
            } while (($response['TotalPages'] >= $response['PageIndex'] + 2));
        } catch (\Throwable $e) {
            $this->log(
                'Error while trying to run product import. ' . $e->getMessage(),
                $importInfo,
                self::IMPORT_ERROR
            );

            throw $e;
        }

        $this->eventManager->dispatch('katana_product_import_after', [
            'importInfo' => $importInfo,
        ]);
    }

    /**
     * Import products
     *
     * @param array $items
     * @param int $page
     * @param KatanaImportInterface $importInfo
     * @throws NoSuchEntityException
     */
    private function importItems(array $items, int $page, KatanaImportInterface $importInfo): void
    {
        //Global scope import
        $this->log(PHP_EOL . 'Processing page ' . $page, $importInfo);
        $this->log('Importing values in global scope', $importInfo);
        $parsedData = $this->dataParser->parse($items);
        $preprocessedData = $this->dataPreprocessor->process($parsedData);

        if (!empty($this->dataPreprocessor->getErrors())) {
            foreach ($this->dataPreprocessor->getErrors() as $error) {
                $this->log(
                    'Error while preprocessing data: ' . $error,
                    $importInfo,
                    self::IMPORT_ERROR
                );
            }
        }

        if (empty($preprocessedData)) {
            return;
        }

        $validatedData = $this->dataValidator->execute($preprocessedData);
        $saveResult = $this->persistenceProcessor->save($validatedData);
        $this->log('Products Created: ' . $saveResult->getCreatedCount(), $importInfo);
        $this->log('Products Updated: ' . $saveResult->getUpdatedCount(), $importInfo);
        $this->log('Products Deleted: ' . $saveResult->getDeletedCount(), $importInfo);

        /** @var Error $error */
        foreach ($saveResult->getErrors() as $error) {
            $this->log(sprintf(
                "Error: %s SKU = %s KatanaPIM ID = %s",
                $error->getMessage(),
                $error->getItemData()['sku'] ?? '',
                $error->getItemData()[AddKatanaPimProductIdAttribute::KATANA_PRODUCT_ID_ATTRIBUTE_CODE] ?? ''
            ), $importInfo, self::IMPORT_ERROR);
        }

        //Store scope import
        $languageMapping = $this->katanaConfig->getLanguageMapping();
        $validItems = $this->filterOutInvalidItems($items, $saveResult);

        if (empty($validItems)) {
            return;
        }

        foreach ($languageMapping as $storeViewId => $languageCode) {
            $this->log('Starting import for ' . $languageCode . ' language in store ' . $storeViewId, $importInfo);

            $parsedData = $this->localizedDataParser->parse(
                $validItems,
                $storeViewId,
                $languageCode
            );

            $validatedData = $this->dataValidator->execute($parsedData, true);

            if (!empty($validatedData)) {
                $saveResult = $this->persistenceProcessor->save($validatedData);
                $this->log('Created: ' . $saveResult->getCreatedCount(), $importInfo);
                $this->log('Updated: ' . $saveResult->getUpdatedCount(), $importInfo);
                $this->log('Deleted: ' . $saveResult->getDeletedCount(), $importInfo);

                foreach ($saveResult->getErrors() as $error) {
                    $this->log(sprintf(
                        "Error: %s. SKU = %s KatanaPIM ID = %s",
                        $error->getMessage(),
                        $error->getItemData()['sku'] ?? '',
                        $error->getItemData()[AddKatanaPimProductIdAttribute::KATANA_PRODUCT_ID_ATTRIBUTE_CODE] ?? ''
                    ), $importInfo, self::IMPORT_ERROR);
                }
            }
        }
    }

    /**
     * Prepare request array.
     *
     * @return Parameters
     */
    private function prepareRequestArray(): Parameters
    {
        $parameters = new Parameters();
        $parameters->fromArray([
            self::REQUEST_PAGE_SIZE_KEY => $this->katanaConfig->getPageSize(),
        ]);

        return $parameters;
    }

    /**
     * Filter out items which threw errors during import
     *
     * @param array $items
     * @param PersistenceResult $saveResult
     * @return array
     */
    private function filterOutInvalidItems(array $items, PersistenceResult $saveResult): array
    {
        $invalidIds = [];
        foreach ($saveResult->getErrors() as $error) {
            $katanaId = $error->getItemData()[AddKatanaPimProductIdAttribute::KATANA_PRODUCT_ID_ATTRIBUTE_CODE] ?? null;
            if ($katanaId) {
                $invalidIds[$katanaId] = $katanaId;
            }
        }

        if (empty($invalidIds)) {
            return $items;
        }

        return array_filter(
            $items,
            function ($item) use ($invalidIds) {
                return !in_array($item['Id'], $invalidIds);
            }
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

            $this->logger->error(
                $string,
                ['entity_type' => $importInfo->getImportType(), 'entity_id' => $importInfo->getImportId()]
            );
        } else {
            if ($this->cliOutput instanceof OutputInterface) {
                $this->cliOutput->writeln('<info>' . $string . '</info>');
            }

            $this->logger->info(
                $string,
                ['entity_type' => $importInfo->getImportType(), 'entity_id' => $importInfo->getImportId()]
            );
        }
    }
}
