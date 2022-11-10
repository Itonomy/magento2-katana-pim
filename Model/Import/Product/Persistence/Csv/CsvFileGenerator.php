<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product\Persistence\Csv;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

class CsvFileGenerator
{
    public const OUTPUT_CSV = 'product_import.csv';

    public const CSV_FILE_DIRECTORY = 'katana_importcsv/';

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * CsvFileGenerator constructor.
     *
     * @param DirectoryList $directoryList
     */
    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    /**
     * Create csv file and store data to it
     *
     * @param array $data
     * @return string
     * @throws FileSystemException
     */
    public function generateFile(array $data): string
    {
        $this->writeFile($data);
        return self::OUTPUT_CSV;
    }

    /**
     * Write data to a csv file
     *
     * @param array $content
     * @return void
     * @throws FileSystemException
     */
    protected function writeFile(array $content): void
    {
        $varDirPath = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $dirPath = $varDirPath . '/' . self::CSV_FILE_DIRECTORY;
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        if (!\is_dir($dirPath) && !\mkdir($dirPath, 0777, true) && !\is_dir($dirPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dirPath));
        }

        $fileName = $dirPath . '/' . self::OUTPUT_CSV;
        $file = \fopen($fileName, 'w');
        $headers = array_keys(reset($content));
        \fputcsv($file, $headers, ',');
        $emptyRow = array_fill_keys($headers, null);

        foreach ($content as $row) {
            $orderedRow = \array_replace($emptyRow, $row);
            \fputcsv($file, $orderedRow, ',');
            //phpcs:enable Magento2.Functions.DiscouragedFunction
        }
    }
}
