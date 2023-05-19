<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\ImportFactory;

/**
 * Cron job for importing Katana product catalog
 */
class ProductsImport
{
    public const JOB_CODE = 'itonomy_katanapim_products_import';

    /**
     * @var ImportFactory
     */
    private ImportFactory $importFactory;

    /**
     * ProductsImport constructor.
     *
     * @param ImportFactory $importFactory
     */
    public function __construct(
        ImportFactory $importFactory
    ) {
        $this->importFactory = $importFactory;
    }

    /**
     * Execute product import
     *
     * @return void
     * @throws \Throwable
     */
    public function execute(): void
    {
        $this->importFactory->get('product')->import();
    }
}
