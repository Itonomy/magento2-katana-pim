<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Console\Command;

use Itonomy\Katanapim\Model\Handler\SpecificationGroup;
use Itonomy\Katanapim\Model\Handler\Specifications;
use Itonomy\Katanapim\Model\Operation\StartImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpecificationsImport extends Command
{
    /**
     * @var ProgressBarFactory
     */
    private ProgressBarFactory $progressBarFactory;

    /**
     * @var StartImport
     */
    private StartImport $startImport;

    /**
     * ImportFromCsv constructor.
     *
     * @param StartImport $startImport
     * @param ProgressBarFactory $progressBarFactory
     */
    public function __construct(
        StartImport $startImport,
        ProgressBarFactory $progressBarFactory
    ) {
        $this->progressBarFactory = $progressBarFactory;
        parent::__construct();

        $this->startImport = $startImport;
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
        $progressBar = $this->progressBarFactory->create([
            'output' => $output,
            'max' => 100,
        ]);
        $progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%message%</info>"
        );
        $progressBar->setMessage(\date('H:i:s') . ' Start');
        $progressBar->start();

        $this->startImport->execute(Specifications::class);
        $this->startImport->execute(SpecificationGroup::class);

        $progressBar->setMessage(\date('H:i:s') . ' Finish');
        $progressBar->finish();

        return 0;
    }
}
