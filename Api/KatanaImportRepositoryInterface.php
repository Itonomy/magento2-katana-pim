<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Api;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;

interface KatanaImportRepositoryInterface
{
    /**
     * Save katana import
     *
     * @param KatanaImportInterface $katanaImport
     * @return KatanaImportInterface
     */
    public function save(KatanaImportInterface $katanaImport): KatanaImportInterface;

    /**
     * Get katana import by id
     *
     * @param $id
     * @return KatanaImportInterface
     */
    public function getById($id): KatanaImportInterface;
}
