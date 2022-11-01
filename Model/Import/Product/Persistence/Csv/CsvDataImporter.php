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
     * Import product data from csv
     *
     * @param $filePath
     * @throws FileSystemException
     */
    public function importData($filePath): void
    {
        $dirPath = $this->directoryList->getPath($this->directoryList::VAR_DIR)
            . '/' . CsvFileGenerator::CSV_FILE_DIRECTORY;
        $totalLinesOfCsv = $this->getTotalLinesOfCsv($dirPath . $filePath);
        $start = $this->dateTime->timestamp();

        try {
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

//                if ($total < $totalLinesOfCsv) {
//                    $this->stripDone($dirPath . $filePath, $total);
//                    $this->importData($filePath);
//                }

                $this->importModel->invalidateIndex();
            }
        } catch (\Exception $e) {
            echo($e->getMessage());
        } finally {
            echo 'Run time: ' . ($this->dateTime->timestamp() - $start) . PHP_EOL;
        }
    }

    /**
     * @param $fileName
     * @return int
     */
    public function getTotalLinesOfCsv($fileName): int
    {
        $products = \fopen($fileName, "r");

        $output = [];

        while (($product = \fgetcsv($products, null, ',')) !== false) {
            $output[] = $product;
        }

        return \count($output) -1;
    }

    /**
     * @param $fileName
     * @param $toRemove
     * @throws FileSystemException
     */
    public function stripDone($fileName, $toRemove): void
    {
        $count = 0;
        //phpcs:ignore Generic.Files.LineLength.TooLong
        $tempCSV = \fopen($this->directoryList->getPath($this->directoryList::VAR_DIR) . '/importcsv/tempoutput.csv', "a+");

        if (($handle = \fopen($fileName, "r")) !== false) {
            while (($data = \fgetcsv($handle)) !== false) {
                if ($count === 0) {
                    \fputcsv($tempCSV, $data);
                }

                if ($count > $toRemove) {
                    \fputcsv($tempCSV, $data);
                }

                $count++;
            }

            \fclose($handle);
            \fclose($tempCSV);
            \unlink($fileName);
            //phpcs:ignore Generic.Files.LineLength.TooLong
            \rename($this->directoryList->getPath($this->directoryList::VAR_DIR) . '/importcsv/tempoutput.csv', $fileName);
        }
    }
}
