<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Process\SpecificationsLocalization;

/**
 * Cron job for importing product Katana specification options and translations
 */
class SpecificationsLocalizationImport
{
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
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @return void
     */
    public function execute(): void
    {
        $this->importer->execute();
    }
}
