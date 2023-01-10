<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product\Persistence\Csv;

use Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image\ImageDirectoryProvider;
use Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResult;
use Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResult\ErrorFactory;
use Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\FileSystem;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import as MagentoImport;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory as MagentoImportFactory;

class CsvDataImporter
{
    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var MagentoImportFactory
     */
    private MagentoImportFactory $importModelFactory;

    /**
     * @var FileSystem
     */
    private FileSystem $fileSystem;

    /**
     * @var ImageDirectoryProvider
     */
    private ImageDirectoryProvider $imagesDirectoryProvider;

    /**
     * @var PersistenceResultFactory
     */
    private PersistenceResultFactory $persistenceResultFactory;

    /**
     * @var ErrorFactory
     */
    private ErrorFactory $errorFactory;

    /**
     * private ErrorFactory $errorFactory;
     *
     * /**
     * @param DirectoryList $directoryList
     * @param MagentoImportFactory $importModelFactory
     * @param FileSystem $fileSystem
     * @param ImageDirectoryProvider $imagesDirectoryProvider
     * @param PersistenceResultFactory $persistenceResultFactory
     * @param ErrorFactory $errorFactory
     */
    public function __construct(
        DirectoryList $directoryList,
        MagentoImportFactory $importModelFactory,
        FileSystem $fileSystem,
        ImageDirectoryProvider $imagesDirectoryProvider,
        PersistenceResultFactory $persistenceResultFactory,
        ErrorFactory $errorFactory
    ) {
        $this->directoryList = $directoryList;
        $this->importModelFactory = $importModelFactory;
        $this->fileSystem = $fileSystem;
        $this->imagesDirectoryProvider = $imagesDirectoryProvider;
        $this->persistenceResultFactory = $persistenceResultFactory;
        $this->errorFactory = $errorFactory;
    }

    /**
     * Import product data from a csv file
     *
     * @param string $filePath
     * @return PersistenceResult
     */
    public function importData(string $filePath): PersistenceResult
    {
        try {
            $dirPath = $this->directoryList->getPath($this->directoryList::VAR_DIR)
                . '/' . CsvFileGenerator::CSV_FILE_DIRECTORY;
            $result = $this->persistenceResultFactory->create();

            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (is_dir($dirPath)) {
                $importModel = $this->importModelFactory->create();
                $importModel->setData(
                    [
                        'entity' => 'catalog_product',
                        'behavior' => MagentoImport::BEHAVIOR_APPEND,
                        //phpcs:ignore Generic.Files.LineLength.TooLong
                        MagentoImport::FIELD_NAME_VALIDATION_STRATEGY => ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS,
                        //phpcs:ignore Generic.Files.LineLength.TooLong
                        MagentoImport::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT => MagentoImport::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT,
                        MagentoImport::FIELD_FIELD_SEPARATOR => ",",
                        MagentoImport::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => ",",
                        MagentoImport::FIELD_NAME_IMG_FILE_DIR => "",
                        'images_base_directory' => $this->imagesDirectoryProvider->getBaseDirectoryRead()
                    ]
                );

                $errorAggregator = $importModel->getErrorAggregator();
                $errorAggregator->initValidationStrategy(
                    $importModel->getData(MagentoImport::FIELD_NAME_VALIDATION_STRATEGY),
                    $importModel->getData(MagentoImport::FIELD_NAME_ALLOWED_ERROR_COUNT)
                );

                $source = Adapter::findAdapterFor(
                    $dirPath . $filePath,
                    $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)
                );
                $validate = $importModel->validateSource($source);

                if (!$validate) {
                    throw new \RuntimeException(
                        'Unable to validate the import CSV file: ' .
                        \json_encode($importModel->getOperationResultMessages($importModel->getErrorAggregator()))
                    );
                }

                $importModel->importSource();

                $result->setCreatedCount((int)$importModel->getCreatedItemsCount());
                $result->setUpdatedCount((int)$importModel->getUpdatedItemsCount());
                $result->setDeletedCount((int)$importModel->getDeletedItemsCount());

                if ($errorAggregator->getErrorsCount() > 0) {
                    $importedData = [];

                    foreach ($source as $i => $data) {
                        $importedData[$i + 1] = $data;
                    }

                    foreach ($errorAggregator->getAllErrors() as $error) {
                        $data = $importedData[$error->getRowNumber()] ?? null;
                        $error = $this->errorFactory->create()
                            ->setMessage($error->getErrorMessage());

                        if ($data) {
                            $error->setItemData($data);
                        }
                        $result->addError($error);
                    }
                }

                $importModel->invalidateIndex();
            }
        } catch (\Exception $e) {
            $result->addError(
                $this->errorFactory->create()->setMessage($e->getMessage())
            );
        }

        return $result;
    }
}
