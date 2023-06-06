<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Console\Command;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Operation\StartImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to import products from KatanaPim
 */
class ImportProducts extends Command
{
    /**
     * @var StartImport
     */
    private StartImport $startImport;

    /**
     * ImportProducts constructor.
     *
     * @param StartImport $startImport
     * @param string|null $name
     */
    public function __construct(
        StartImport $startImport,
        string $name = null
    ) {
        $this->startImport = $startImport;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('katana:import:products');
        $this->setDescription('Import products from Katana pim');
        parent::configure();
    }

    /**
     * CLI command description
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
        $start = \microtime(true);
        $output->writeln('Start: ' . \date('H:i:s'));
        $this->startImport->execute(KatanaImportInterface::PRODUCT_IMPORT_TYPE, $output);
        $output->writeln('Executed: ' . \ceil(\microtime(true) - $start) . ' seconds');

        return 0;
    }
}
