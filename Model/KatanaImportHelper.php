<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\ResourceModel\KatanaImport\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class KatanaImportHelper
{
    /**
     * @var KatanaImportRepository
     */
    private KatanaImportRepository $katanaImportRepository;

    /**
     * @var KatanaImportFactory
     */
    private KatanaImportFactory $katanaImportFactory;

    /**
     * @var KatanaImportInterface
     */
    private KatanaImportInterface $katanaImport;

    /**
     * @param KatanaImportRepository $katanaImportRepository
     * @param KatanaImportFactory $katanaImportFactory
     */
    public function __construct(
        KatanaImportRepository $katanaImportRepository,
        KatanaImportFactory    $katanaImportFactory
    ) {
        $this->katanaImportRepository = $katanaImportRepository;
        $this->katanaImportFactory = $katanaImportFactory;
    }

    /**
     * @param string $entityType
     * @param string $status
     * @param string|null $importId
     * @return KatanaImportInterface
     * @throws CouldNotSaveException
     */
    public function createKatanaImport(
        string $entityType,
        string $status,
        ?string $importId
    ): KatanaImportInterface {
        $katanaImport = $this->katanaImportFactory->create();
        $katanaImport->setEntityType($entityType);
        $katanaImport->setStatus($status);
        $katanaImport->setImportId($importId);
        $katanaImport->setFinishTime(null);
        return $this->katanaImportRepository->save($katanaImport);
    }

    /**
     * @param KatanaImportInterface $katanaImport
     * @param string $status
     * @return void
     * @throws CouldNotSaveException
     */
    public function updateKatanaImportStatus(KatanaImportInterface $katanaImport, string $status): void
    {
        $katanaImport->setStatus($status);
        $this->katanaImportRepository->save($katanaImport);
    }

    /**
     * @param KatanaImportInterface $katanaImport
     * @return void
     */
    public function setImport(KatanaImportInterface $katanaImport)
    {
        $this->katanaImport = $katanaImport;
    }

    public function getImport(): KatanaImportInterface
    {
        return $this->katanaImport;
    }
}
