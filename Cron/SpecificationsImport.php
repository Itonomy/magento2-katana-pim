<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\ImportFactory;

/**
 * Cron job for importing product Katana specifications
 */
class SpecificationsImport
{
    public const JOB_CODE = 'itonomy_katanapim_specifications_import';

    /**
     * @var ImportFactory
     */
    private ImportFactory $importFactory;

    /**
     * SpecificationsImport constructor.
     *
     * @param ImportFactory $importFactory
     */
    public function __construct(
        ImportFactory $importFactory
    ) {
        $this->importFactory = $importFactory;
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
        $this->importFactory->get('specifications')->import();
        $this->importFactory->get('specifications_group')->import();
    }
}
