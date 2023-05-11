<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\ResourceModel\KatanaImport\CollectionFactory;
use Magento\Cron\Model\Schedule;
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
     * @var ResourceModel\KatanaImport\CollectionFactory
     */
    private ResourceModel\KatanaImport\CollectionFactory $katanaCollectionFactory;

    /**
     * @param KatanaImportRepository $katanaImportRepository
     * @param KatanaImportFactory $katanaImportFactory
     * @param ResourceModel\KatanaImport\CollectionFactory $katanaCollectionFactory
     */
    public function __construct(
        KatanaImportRepository $katanaImportRepository,
        KatanaImportFactory    $katanaImportFactory,
        CollectionFactory      $katanaCollectionFactory
    ) {
        $this->katanaImportRepository = $katanaImportRepository;
        $this->katanaImportFactory = $katanaImportFactory;
        $this->katanaCollectionFactory = $katanaCollectionFactory;
    }

    /**
     * @param string $entityType
     * @param string $status
     * @param int|null $importId
     * @return KatanaImportInterface
     * @throws CouldNotSaveException
     */
    public function createKatanaImport(
        string $entityType,
        string $status,
        ?int $importId
    ): KatanaImportInterface {
        $katanaImport = $this->katanaImportFactory->create();
        $katanaImport->setEntityType($entityType);
        $katanaImport->setStatus($status);
        $katanaImport->setImportId($importId);
        $katanaImport->setFinishTime(null);
        return $this->katanaImportRepository->save($katanaImport);
    }


    /**
     * Get existing imports from katanapim_import
     *
     * @param array $jobCodes
     * @param array|null $statuses
     * @param int|null $limit
     * @param string|null $startTime
     * @param string|null $finishTime
     * @return array
     */
    public function getKatanaImports(
        array $jobCodes,
        ?array $statuses = null,
        ?int $limit = 1000,
        ?string $startTime = null,
        ?string $finishTime = null
    ): array {
        $collection = $this->katanaCollectionFactory->create();
        $collection->addFieldToFilter('entity_type', ['in' => $jobCodes]);
        $collection->setOrder(
            'start_time',
            $collection::SORT_ORDER_DESC
        );
        $collection->setPageSize($limit);

        if ($startTime !== null) {
            $collection->addFieldToFilter('start_time', $startTime);
        }

        if ($finishTime !== null) {
            $collection->addFieldToFilter('finish_time', $finishTime);
        }

        if ($statuses !== null) {
            $collection->addFieldToFilter('status', ['in' => $statuses]);
        }

        return $collection->getItems() ?? [];
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
}
