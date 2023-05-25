<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\ImportInterface;
use Magento\Framework\ObjectManagerInterface;

class ImportFactory
{
    /**
     * @var array
     */
    private array $registry;

    /**
     * @var array
     */
    private array $importTypes;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var KatanaImportHelper
     */
    private KatanaImportHelper $importHelper;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param KatanaImportHelper $importHelper
     * @param array $importTypes
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        KatanaImportHelper $importHelper,
        array $importTypes = []
    ) {
        $this->objectManager = $objectManager;
        $this->importTypes = $importTypes;
        $this->importHelper = $importHelper;
    }

    /**
     * Returns import object
     *
     * @param string $typeName
     * @return ImportInterface
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function get(string $typeName): ImportInterface
    {
        if (!isset($this->importTypes[$typeName])) {
            throw new \InvalidArgumentException(
                \sprintf('Import for type %s not registered', $typeName)
            );
        }
        if (!isset($this->registry[$typeName])) {
            $this->registry[$typeName] = $this->objectManager->get($this->importTypes[$typeName]);
            $katanaImport = $this->importHelper->createKatanaImport(
                $this->registry[$typeName]->getEntityType(),
                KatanaImport::STATUS_PENDING,
                $this->registry[$typeName]->getEntityId()
            );
            $this->registry[$typeName]->setKatanaImport($katanaImport);
        }

        return $this->registry[$typeName];
    }
}
