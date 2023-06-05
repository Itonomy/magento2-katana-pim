<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;

interface ImportRunnerInterface
{
    /**
     * @param KatanaImportInterface $importData
     * @return void
     */
    public function execute(KatanaImportInterface $importData): void;
}