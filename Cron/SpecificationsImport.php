<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Handler\SpecificationGroup;
use Itonomy\Katanapim\Model\Handler\Specifications;
use Itonomy\Katanapim\Model\Operation\StartImport;

/**
 * Cron job for importing product Katana specifications
 */
class SpecificationsImport
{
    public const JOB_CODE = 'itonomy_katanapim_specifications_import';

    /**
     * @var StartImport
     */
    private StartImport $startImport;

    /**
     * @param StartImport $startImport
     */
    public function __construct(StartImport $startImport)
    {
        $this->startImport = $startImport;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Throwable
     */
    public function execute(): void
    {
        $this->startImport->execute(Specifications::class);
        $this->startImport->execute(SpecificationGroup::class);
    }
}
