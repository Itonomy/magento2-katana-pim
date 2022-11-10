<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product\Persistence\Csv;

use Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image\ImageDirectoryProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\FileSystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Model\Import as MagentoImport;

class CsvDataImporter
{
    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var MagentoImport
     */
    private MagentoImport $importModel;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var FileSystem
     */
    private FileSystem $fileSystem;

    /**
     * @var ImageDirectoryProvider
     */
    private ImageDirectoryProvider $imagesDirectoryProvider;

    /**
     * CsvDataImporter constructor.
     *
     * @param DirectoryList $directoryList
     * @param MagentoImport $importModel
     * @param DateTime $dateTime
     * @param FileSystem $fileSystem
     * @param ImageDirectoryProvider $imagesDirectoryProvider
     */
    public function __construct(
        DirectoryList $directoryList,
        MagentoImport $importModel,
        DateTime $dateTime,
        FileSystem $fileSystem,
        ImageDirectoryProvider $imagesDirectoryProvider
    ) {
        $this->directoryList = $directoryList;
        $this->importModel = $importModel;
        $this->dateTime = $dateTime;
        $this->fileSystem = $fileSystem;
        $this->imagesDirectoryProvider = $imagesDirectoryProvider;
    }

    /**
     * Import product data from a csv file
     *
     * @param string $filePath
     * @throws FileSystemException
     */
    public function importData(string $filePath): void
    {
        $dirPath = $this->directoryList->getPath($this->directoryList::VAR_DIR)
            . '/' . CsvFileGenerator::CSV_FILE_DIRECTORY;
        $start = $this->dateTime->timestamp();

        try {
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (is_dir($dirPath)) {
                $this->importModel->setData(
                    [
                        'entity' => 'catalog_product',
                        'behavior' => MagentoImport::BEHAVIOR_APPEND,
                        'validation_strategy' => 'validation-stop-on-errors',
                        'import_empty_attribute_value_constant' => "__EMPTY__VALUE__",
                        '_import_field_separator' => ",",
                        '_import_multiple_value_separator' => ",",
                        'import_images_file_dir'=> "",
                        'images_base_directory'=> $this->imagesDirectoryProvider->getBaseDirectoryRead()
                    ]
                );

                $errorAggregator = $this->importModel->getErrorAggregator();
                $errorAggregator->initValidationStrategy(
                    $this->importModel->getData(MagentoImport::FIELD_NAME_VALIDATION_STRATEGY),
                    $this->importModel->getData(MagentoImport::FIELD_NAME_ALLOWED_ERROR_COUNT)
                );

                $validate = $this->importModel->validateSource(
                    \Magento\ImportExport\Model\Import\Adapter::findAdapterFor(
                        $dirPath . $filePath,
                        $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)
                    )
                );

                if (!$validate) {
                    throw new \Exception(
                        'Unable to validate the CSV: ' .
                        //phpcs:ignore Generic.Files.LineLength.TooLong
                        \json_encode($this->importModel->getOperationResultMessages($this->importModel->getErrorAggregator()))
                    );
                }

                // phpcs:disable Magento2.Security.LanguageConstruct
                echo 'Validation passed!' . PHP_EOL;
                $this->importModel->importSource();
                $created = (int)$this->importModel->getCreatedItemsCount();
                $updated = (int)$this->importModel->getUpdatedItemsCount();
                $deleted = (int)$this->importModel->getDeletedItemsCount();
                $total = $created + $updated + $deleted;

                echo 'Import Complete' . PHP_EOL;
                echo 'New Items: ' . $created . PHP_EOL;
                echo 'Updated Items: ' . $updated . PHP_EOL;
                echo 'Deleted Items: ' . $deleted . PHP_EOL;
                echo 'Total Items Handled: ' . $total . PHP_EOL;

                $this->importModel->invalidateIndex();
            }
        } catch (\Exception $e) {
            echo($e->getMessage());
        } finally {
            echo 'Run time: ' . ($this->dateTime->timestamp() - $start) . PHP_EOL;
        }
        // phpcs:enable Magento2.Security.LanguageConstruct
    }
}
