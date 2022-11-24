<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Import\Product\ProductImport;

/**
 * Cron job for importing Katana product catalog
 */
class ProductsImport
{
    public const JOB_CODE = 'itonomy_katanapim_products_import';
    /**
     * @var ProductImport
     */
    private ProductImport $importer;

    /**
     * ProductsImport constructor.
     *
     * @param ProductImport $importer
     */
    public function __construct(
        ProductImport $importer
    ) {
        $this->importer = $importer;
    }

    /**
     * Execute product import
     *
     * @return void
     * @throws \Throwable
     */
    public function execute(): void
    {
        $this->importer->import();
    }
}
