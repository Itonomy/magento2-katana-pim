<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Operation\StartImport;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Cron job for importing product Katana specification options and translations
 */
class SpecificationsLocalizationImport
{
    public const JOB_CODE = 'itonomy_katanapim_specifications_localization_import';

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
     * Import specification (attribute) options, specification translation and option translations
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Throwable
     */
    public function execute(): void
    {
        $this->startImport->execute(KatanaImportInterface::SPECIFICATION_LOCALIZATION_IMPORT_TYPE);
    }
}
