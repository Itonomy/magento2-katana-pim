<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Process\SpecificationsLocalization;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Cron job for importing product Katana specification options and translations
 */
class SpecificationsLocalizationImport
{
    public const JOB_CODE = 'itonomy_katanapim_specifications_localization_import';

    /**
     * @var SpecificationsLocalization
     */
    private SpecificationsLocalization $importer;

    /**
     * SpecificationsLocalizationImport constructor.
     *
     * @param SpecificationsLocalization $importer
     */
    public function __construct(
        SpecificationsLocalization $importer
    ) {
        $this->importer = $importer;
    }

    /**
     * Import specification (attribute) options, specification translation and option translations
     *
     * @throws CouldNotSaveException
     * @throws RuntimeException
     * @return void
     */
    public function execute(): void
    {
        $this->importer->execute();
    }
}
