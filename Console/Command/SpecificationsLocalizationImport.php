<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Console\Command;

use Itonomy\Katanapim\Model\Handler\SpecificationsLocalization;
use Itonomy\Katanapim\Model\Operation\StartImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBarFactory;

/**
 * Class for importing specification (attribute) translations and their options and option translations
 */
class SpecificationsLocalizationImport extends Command
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
     * SpecificationsLocalizationImport constructor.
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
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('katana:import:specifications:localization')
            ->setDescription('Import product specifications localization');
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

        $this->startImport->execute(SpecificationsLocalization::class);

        $progressBar->setMessage(\date('H:i:s') . ' Finish');
        $progressBar->finish();

        return 0;
    }
}
