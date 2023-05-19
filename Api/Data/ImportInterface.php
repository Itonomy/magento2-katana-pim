<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface ImportInterface
{
    public const PRODUCT_IMPORT_JOB_CODE = 'itonomy_katanapim_products_import';
    public const SPECIFICATIONS_IMPORT_JOB_CODE = 'itonomy_katanapim_specifications_import';
    public const SPECIFICATIONS_LOCALIZATION_IMPORT_JOB_CODE = 'itonomy_katanapim_specifications_localization_import';

    /**
     * @return void
     */
    public function import();

    /**
     * @return string
     */
    public function getEntityType(): string;

    /**
     * @return string
     */
    public function getEntityId(): string;

    /**
     * @param KatanaImportInterface $katanaImport
     * @return void
     */
    public function setKatanaImport(KatanaImportInterface $katanaImport): void;

    /**
     * @return KatanaImportInterface
     */
    public function getKatanaImport(): KatanaImportInterface;
}
