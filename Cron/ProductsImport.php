<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\Config\Katana;
use Itonomy\Katanapim\Model\Handler\ProductImport;
use Itonomy\Katanapim\Model\Operation\StartImport;

/**
 * Cron job for importing Katana product catalog
 */
class ProductsImport
{
    public const JOB_CODE = 'itonomy_katanapim_products_import';

    /**
     * @var StartImport
     */
    private StartImport $startImport;

    /**
     * @var Katana
     */
    private Katana $config;

    /**
     * ProductsImport constructor.
     *
     * @param StartImport $startImport
     * @param Katana $config
     */
    public function __construct(
        StartImport $startImport,
        Katana $config
    ) {
        $this->startImport = $startImport;
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
            $this->startImport->execute(ProductImport::class);
        }
    }
}
