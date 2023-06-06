<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Builder;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\KatanaImportFactory;

class BuildImportDataByImportType
{
    /**
     * @var KatanaImportFactory
     */
    private KatanaImportFactory $katanaImportFactory;

    /**
     * @param KatanaImportFactory $katanaImportFactory
     */
    public function __construct(KatanaImportFactory $katanaImportFactory)
    {
        $this->katanaImportFactory = $katanaImportFactory;
    }

    /**
     * Get import data
     *
     * @param string $importType
     * @return KatanaImportInterface
     */
    public function execute(string $importType): KatanaImportInterface
    {
        $importData = $this->katanaImportFactory->create();
        $importData->setImportId(uniqid());
        $importData->setImportType($importType);
        $importData->setStatus(KatanaImportInterface::STATUS_PENDING);
        $importData->setStartTime((new \DateTime())->format('Y-m-d H:i:s'));

        return $importData;
    }
}
