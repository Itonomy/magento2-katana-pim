<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Console\Command;

use Itonomy\Katanapim\Model\Process\Entity\Specifications;
use Itonomy\Katanapim\Model\Process\Entity\SpecificationGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBarFactory;

class SpecificationsImport extends Command
{
    private Specifications $specifications;
    private SpecificationGroup $specificationGroup;
    private ProgressBarFactory $progressBarFactory;

    /**
     * ImportFromCsv constructor.
     *
     * @param Specifications $specifications
     * @param SpecificationGroup $specificationGroup
     * @param ProgressBarFactory $progressBarFactory
     */
    public function __construct(
        Specifications $specifications,
        SpecificationGroup $specificationGroup,
        ProgressBarFactory $progressBarFactory
    ) {
        $this->specifications = $specifications;
        $this->specificationGroup = $specificationGroup;
        $this->progressBarFactory = $progressBarFactory;

        parent::__construct();
    }

    /**
     * Set the name and description
     */
    protected function configure()
    {
        $this->setName('katana:import:specifications')
            ->setDescription('Import product\'s specifications');
        parent::configure();
    }

    /**
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

        $this->specifications
            ->setProgressBar($progressBar)
            ->execute();

        $this->specificationGroup
            ->execute();

        $progressBar->setMessage(\date('H:i:s') . ' Finish');
        $progressBar->finish();

        return 0;
    }
}
