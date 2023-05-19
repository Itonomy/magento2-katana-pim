<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Console\Command;

use Itonomy\Katanapim\Model\Import\Product\ProductImport;
use Itonomy\Katanapim\Model\ImportFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBarFactory;

/**
 * CLI Command to import products from KatanaPim
 */
class ImportProducts extends Command
{
    /**
     * @var ProgressBarFactory
     */
    private ProgressBarFactory $progressBarFactory;

    /**
     * @var ImportFactory
     */
    private ImportFactory $importFactory;

    /**
     * ImportProducts constructor.
     *
     * @param ImportFactory $importFactory
     * @param ProgressBarFactory $progressBarFactory
     * @param string|null $name
     */
    public function __construct(
        ImportFactory $importFactory,
        ProgressBarFactory $progressBarFactory,
        string $name = null
    ) {
        $this->progressBarFactory = $progressBarFactory;
        $this->importFactory = $importFactory;
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

        $progressBar = $this->progressBarFactory->create([
            'output' => $output,
            'max' => 100
        ]);
        $progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%message%</info>"
        );
        $progressBar->setMessage(\date('H:i:s') . ' Start');
        $progressBar->start();

        $this->importFactory->get('product')->setCliOutput($output);
        $this->importFactory->get('product')->import();

        $progressBar->setMessage(\date('H:i:s') . ' Finish');
        $progressBar->finish();

        $output->writeln('Executed: ' . \ceil(\microtime(true) - $start) . ' seconds');

        return 0;
    }
}
