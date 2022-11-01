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

    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    public function generateFile($data): string
    {
        $this->writeFile($data);
        return self::OUTPUT_CSV;
    }

    /**
     * @param array $content
     * @throws FileSystemException
     */
    protected function writeFile(array $content)
    {
        $varDirPath = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $dirPath = $varDirPath . '/' . self::CSV_FILE_DIRECTORY;

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
        }
    }
}
