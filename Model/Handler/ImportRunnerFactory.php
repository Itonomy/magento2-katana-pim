<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;

class ImportRunnerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var array|string[]
     */
    private array $importRunners;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $importRunners
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $importRunners = []
    ) {
        $this->objectManager = $objectManager;
        $this->importRunners = $importRunners;
    }

    /**
     * Create import runner
     *
     * @param string $importType
     * @return ImportRunnerInterface
     * @throws NotFoundException
     */
    public function create(string $importType): ImportRunnerInterface
    {
        if (!isset($this->importRunners[$importType])) {
            throw new NotFoundException(__("Import with type {$importType} doesn't exist."));
        }

        return $this->objectManager->create($this->importRunners[$importType]);
    }
}
