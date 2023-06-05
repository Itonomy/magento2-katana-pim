<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Builder;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;

class BuildImportDataByImportType
{
    /**
     * @var \Itonomy\Katanapim\Model\KatanaImportFactory
     */
    private \Itonomy\Katanapim\Model\KatanaImportFactory $katanaImportFactory;

    /**
     * @param \Itonomy\Katanapim\Model\KatanaImportFactory $katanaImportFactory
     */
    public function __construct(\Itonomy\Katanapim\Model\KatanaImportFactory $katanaImportFactory)
    {
        $this->katanaImportFactory = $katanaImportFactory;
    }

    /**
     * @param string $importType
     * @return KatanaImportInterface
     */
    public function execute(string $importType): KatanaImportInterface
    {
        $object = $this->katanaImportFactory->create();
        $object->setImportId(uniqid());
        $object->setStatus(KatanaImportInterface::STATUS_PENDING);
        $object->setStartTime((new \DateTime())->format('Y-m-d H:i:s'));

        return $object;
    }
}
