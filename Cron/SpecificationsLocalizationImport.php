<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\ImportFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Cron job for importing product Katana specification options and translations
 */
class SpecificationsLocalizationImport
{
    public const JOB_CODE = 'itonomy_katanapim_specifications_localization_import';

    /**
     * @var ImportFactory
     */
    private ImportFactory $importFactory;

    /**
     * @param ImportFactory $importFactory
     */
    public function __construct(ImportFactory $importFactory)
    {
        $this->importFactory = $importFactory;
    }

    /**
     * Import specification (attribute) options, specification translation and option translations
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(): void
    {
        $this->importFactory->get('specifications_localization')->import();
    }
}
