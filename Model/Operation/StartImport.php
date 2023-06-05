<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Operation;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Builder\BuildImportDataByImportType;
use Itonomy\Katanapim\Model\Handler\ImportRunnerFactory;
use Itonomy\Katanapim\Model\KatanaImportRepository;

class StartImport
{
    /**
     * @var KatanaImportRepository
     */
    private KatanaImportRepository $katanaImportRepository;

    /**
     * @var BuildImportDataByImportType
     */
    private BuildImportDataByImportType $buildImportDataByImportType;

    /**
     * @var ImportRunnerFactory
     */
    private ImportRunnerFactory $importRunnerFactory;

    /**
     * @param BuildImportDataByImportType $buildImportDataByImportType
     * @param KatanaImportRepository $katanaImportRepository
     * @param ImportRunnerFactory $importRunnerFactory
     */
    public function __construct(
        BuildImportDataByImportType $buildImportDataByImportType,
        KatanaImportRepository $katanaImportRepository,
        ImportRunnerFactory $importRunnerFactory
    ) {
        $this->buildImportDataByImportType = $buildImportDataByImportType;
        $this->katanaImportRepository = $katanaImportRepository;
        $this->importRunnerFactory = $importRunnerFactory;
    }

    /**
     * @param string $importType
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Throwable
     */
    public function execute(string $importType): void
    {
        $importData = $this->buildImportDataByImportType->execute($importType);
        $import = $this->importRunnerFactory->create($importType);

        try {
            $importData->setStatus(KatanaImportInterface::STATUS_RUNNING);
            $import->execute($importData);
        } catch (\Throwable $e) {
            $importData->setStatus(KatanaImportInterface::STATUS_ERROR);
            $importData->setFinishTime((new \DateTime())->format('Y-m-d H:i:s'));
            $this->katanaImportRepository->save($importData);
            throw $e;
        }
        $importData->setStatus(KatanaImportInterface::STATUS_COMPLETE);
        $importData->setFinishTime((new \DateTime())->format('Y-m-d H:i:s'));
        $this->katanaImportRepository->save($importData);
    }
}
