<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Console\Command;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\Operation\StartImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class for importing specification (attribute) translations and their options and option translations
 */
class SpecificationsLocalizationImport extends Command
{
    /**
     * @var StartImport
     */
    private StartImport $startImport;

    /**
     * SpecificationsLocalizationImport constructor.
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
        $this->startImport->execute(KatanaImportInterface::SPECIIFICATION_LOCALIZATION_IMPORT_TYPE, $output);
        return 0;
    }
}
