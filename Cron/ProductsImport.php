<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Import\Product\ProductImport;

/**
 * Cron job for importing Katana product catalog
 */
class ProductsImport
{
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
     * @return void
     */
    public function execute(): void
    {
        $this->importer->import();
    }
}
