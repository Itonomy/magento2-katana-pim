<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Api;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;

interface KatanaImportRepositoryInterface
{
    /**
     * @param KatanaImportInterface $katanaImport
     * @return KatanaImportInterface
     */
    public function save(KatanaImportInterface $katanaImport): KatanaImportInterface;

    /**
     * @param $id
     * @return KatanaImportInterface
     */
    public function getById($id): KatanaImportInterface;
}
