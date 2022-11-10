<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product\Persistence;

use Itonomy\Katanapim\Model\Import\Product\Persistence\Csv\CsvDataImporter;
use Itonomy\Katanapim\Model\Import\Product\Persistence\Csv\CsvFileGenerator;
use Itonomy\Katanapim\Model\Import\Product\PersistenceProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;

class CsvProcessor implements PersistenceProcessorInterface
{
    /**
     * @var Csv\CsvDataImporter
     */
    private CsvDataImporter $csvDataImporter;

    /**
     * @var CsvFileGenerator
     */
    private CsvFileGenerator $csvFileGenerator;

    /**
     * CsvProcessor constructor.
     *
     * @param CsvDataImporter $csvDataImporter
     * @param CsvFileGenerator $csvFileGenerator
     */
    public function __construct(
        CsvDataImporter $csvDataImporter,
        CsvFileGenerator $csvFileGenerator
    ) {
        $this->csvDataImporter = $csvDataImporter;
        $this->csvFileGenerator = $csvFileGenerator;
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @throws CouldNotSaveException|FileSystemException
     */
    public function save(array $data): void
    {
        $fileName = $this->csvFileGenerator->generateFile($data);

        if ($fileName) {
            $this->csvDataImporter->importData($fileName);
        } else {
            throw new CouldNotSaveException(__('Failed to generate product import csv file.'));
        }
    }
}
