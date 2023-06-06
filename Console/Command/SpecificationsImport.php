<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Console\Command;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Operation\StartImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpecificationsImport extends Command
{
    /**
     * @var StartImport
     */
    private StartImport $startImport;

    /**
     * ImportFromCsv constructor.
     *
     * @param StartImport $startImport
     */
    public function __construct(
        StartImport $startImport
    ) {
        $this->startImport = $startImport;
        parent::__construct();
    }

    /**
     * Set the name and description
     */
    protected function configure(): void
    {
        $this->setName('katana:import:specifications')
            ->setDescription('Import product\'s specifications');
        parent::configure();
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Throwable
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->startImport->execute(KatanaImportInterface::SPECIFICATION_IMPORT_TYPE, $output);
        $this->startImport->execute(KatanaImportInterface::SPECIFICATION_GROUP_IMPORT_TYPE, $output);
        return 0;
    }
}
