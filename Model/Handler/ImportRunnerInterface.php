<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ImportRunnerInterface
{
    /**
     * @param KatanaImportInterface $importInfo
     * @return void
     */
    public function execute(KatanaImportInterface $importInfo): void;

    /**
     * @param OutputInterface $cliOutput
     * @return void
     */
    public function setCliOutput(OutputInterface $cliOutput): void;
}
