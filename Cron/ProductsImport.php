<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Cron;

use Itonomy\Katanapim\Model\ImportFactory;
use Itonomy\Katanapim\Model\Config\Katana;

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
     * @var Katana
     */
    private Katana $config;

    /**
     * ProductsImport constructor.
     *
     * @param ImportFactory $importFactory
     * @param Katana $config
     */
    public function __construct(
        ImportFactory $importFactory,
        Katana $config
    ) {
        $this->importFactory = $importFactory;
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
            $this->importFactory->get('product')->import();
        }
    }
}
