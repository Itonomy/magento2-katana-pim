<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product;

use Itonomy\Katanapim\Model\Config\Katana;
use Itonomy\Katanapim\Model\Data\Product\DataParser;
use Itonomy\Katanapim\Model\Data\Product\LocalizedDataParser;
use Itonomy\Katanapim\Model\Data\Product\DataPreprocessor;
use Itonomy\Katanapim\Model\Logger;
use Itonomy\Katanapim\Model\RestClient;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Framework\Event\ManagerInterface;

class ProductImport
{
    private const URL_PART = 'Product';
    private const REQUEST_PAGE_INDEX_KEY = 'filterModel.paging.pageIndex';
    private const REQUEST_PAGE_SIZE_KEY = 'filterModel.paging.pageSize';

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
     * @var int
     */
    private int $pageSize;

    /**
     * @var LocalizedDataParser
     */
    private LocalizedDataParser $localizedDataParser;

    /**
     * @var Katana
     */
    private Katana $katanaConfig;

    /**
     * ProductImport constructor.
     *
     * @param RestClient $restClient
     * @param DataParser $dataParser
     * @param LocalizedDataParser $localizedDataParser
     * @param DataPreprocessor $dataPreprocessor
     * @param PersistenceProcessorInterface $persistenceProcessor
     * @param ManagerInterface $eventManager
     * @param Logger $logger
     * @param Katana $katanaConfig
     * @param int $pageSize
     */
    public function __construct(
        RestClient $restClient,
        DataParser $dataParser,
        LocalizedDataParser $localizedDataParser,
        DataPreprocessor $dataPreprocessor,
        PersistenceProcessorInterface $persistenceProcessor,
        ManagerInterface $eventManager,
        Logger $logger,
        Katana $katanaConfig,
        int $pageSize = 2000
    ) {
        $this->restClient = $restClient;
        $this->dataParser = $dataParser;
        $this->localizedDataParser = $localizedDataParser;
        $this->dataPreprocessor = $dataPreprocessor;
        $this->persistenceProcessor = $persistenceProcessor;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->katanaConfig = $katanaConfig;
        $this->pageSize = $pageSize;
    }

    /**
     * Import products from Katana PIM
     *
     * @return void
     * @throws \Throwable
     */
    public function import(): void
    {
        $page = 0;
        $parameters = $this->prepareRequestArray();

        try {
            //TODO: think about getting all products data before processing raw data
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

                $this->importItems($items);
            } while (($response['TotalPages'] >= $response['PageIndex'] + 2));
        } catch (\Throwable $e) {
            $this->logger->critical('Error while trying to run katana product import. ' . $e->getMessage());
            throw $e;
        }

        $this->eventManager->dispatch('katana_product_import_after');
    }

    /**
     * @param $items
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function importItems($items): void
    {
        //Global scope import
        //TODO: carry all parsed data for all products due to configurable product creation
        $parsedData = $this->dataParser->parse($items);
        $preprocessedData = $this->dataPreprocessor->process($parsedData);

        if (!empty($preprocessedData)) {
            $this->persistenceProcessor->save($preprocessedData);
        }

        //Store scope import
        $languageMapping = $this->katanaConfig->getLanguageMapping();

        foreach ($languageMapping as $storeViewId => $languageCode) {
            $parsedData = $this->localizedDataParser->parse(
                $items,
                $storeViewId,
                $languageCode
            );

            if (!empty($parsedData)) {
                $this->persistenceProcessor->save($parsedData);
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
            self::REQUEST_PAGE_SIZE_KEY => $this->pageSize,
        ]);

        return $parameters;
    }
}
