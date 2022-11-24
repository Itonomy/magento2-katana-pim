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
    public const JOB_CODE = 'itonomy_katanapim_specifications_import';

    /**
     * @var Specifications
     */
    private Specifications $specifications;

    /**
     * @var SpecificationGroup
     */
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
     * Execute specifications import
     *
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
