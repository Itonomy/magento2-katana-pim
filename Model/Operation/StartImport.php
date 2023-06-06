<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Operation;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Builder\BuildImportDataByImportType;
use Itonomy\Katanapim\Model\Handler\ImportRunnerFactory;
use Itonomy\Katanapim\Model\KatanaImportRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NotFoundException;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param OutputInterface|null $cliOutput
     * @return void
     * @throws CouldNotSaveException
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function execute(string $importType, OutputInterface $cliOutput = null): void
    {
        $importInfo = $this->buildImportDataByImportType->execute($importType);
        $import = $this->importRunnerFactory->create($importType);

        if ($cliOutput) {
            $import->setCliOutput($cliOutput);
        }

        try {
            $importInfo->setStatus(KatanaImportInterface::STATUS_RUNNING);
            $this->katanaImportRepository->save($importInfo);
            $import->execute($importInfo);
        } catch (\Throwable $e) {
            $importInfo->setStatus(KatanaImportInterface::STATUS_ERROR);
            $importInfo->setFinishTime((new \DateTime())->format('Y-m-d H:i:s'));
            $this->katanaImportRepository->save($importInfo);

            throw $e;
        }

        $importInfo->setStatus(KatanaImportInterface::STATUS_COMPLETE);
        $importInfo->setFinishTime((new \DateTime())->format('Y-m-d H:i:s'));
        $this->katanaImportRepository->save($importInfo);
    }
}
