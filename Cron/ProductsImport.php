<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Config\Katana;
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
     * @var Katana
     */
    private Katana $config;

    /**
     * ProductsImport constructor.
     *
     * @param ProductImport $importer
     * @param Katana $config
     */
    public function __construct(
        ProductImport $importer,
        Katana $config
    ) {
        $this->importer = $importer;
        $this->config = $config;
    }

    /**
     * Execute product import
     *
     * @return void
     * @throws \Throwable
     */
    public function execute(): void
    {
        if ($this->config->isProductImportEnabled()) {
            $this->importer->import();
        }
    }
}
