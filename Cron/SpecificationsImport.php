<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Process\Entity\Specifications;
use Itonomy\Katanapim\Model\Process\Entity\SpecificationGroup;

/**
 * Cron job for importing product Katana specifications
 */
class SpecificationsImport
{
    private Specifications $specifications;
    private SpecificationGroup $specificationGroup;

    /**
     * SpecificationsImport constructor.
     *
     * @param Specifications $specifications
     * @param SpecificationGroup $specificationGroup
     */
    public function __construct(
        Specifications $specifications,
        SpecificationGroup $specificationGroup
    ) {
        $this->specifications = $specifications;
        $this->specificationGroup = $specificationGroup;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function execute(): void
    {
        $this->specifications->execute();
        $this->specificationGroup->execute();
    }
}
