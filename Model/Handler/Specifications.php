<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Handler;

use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\AttributeMappingRepository;
use Itonomy\Katanapim\Model\RestClient;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Specifications implements ImportRunnerInterface
{
    private const URL_PART = 'Specifications';

    /**
     * @var string[]
     */
    public array $type = [
        0 => 'select',
        10 => 'text', //more than 255
        20 => 'text'
    ];

    public const IMPORT_ERROR = 'error';
    public const IMPORT_INFO = 'info';

    /**
     * @var RestClient
     */
    private RestClient $rest;

    /**
     * @var AttributeMappingRepository
     */
    private AttributeMappingRepository $attributeMappingRepository;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var OutputInterface|null
     */
    private ?OutputInterface $cliOutput;

    /**
     * @param RestClient $rest
     * @param AttributeMappingRepository $attributeMappingRepository
     * @param Logger $logger
     */
    public function __construct(
        RestClient $rest,
        AttributeMappingRepository $attributeMappingRepository,
        Logger $logger
    ) {
        $this->rest = $rest;
        $this->attributeMappingRepository = $attributeMappingRepository;
        $this->logger = $logger;
        $this->cliOutput = null;
    }

    /**
     * Execute specifications import
     *
     * @param KatanaImportInterface $importInfo
     * @return void
     * @throws CouldNotSaveException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function execute(KatanaImportInterface $importInfo): void
    {
        $i = 0;
        try {
            do {
                $parameters = new Parameters();
                $parameters->set('specificationFilterModel.pageIndex', $i);

                $specifications = $this->rest->execute(self::URL_PART, Request::METHOD_GET, $parameters);

                if (empty($specifications['Items'])) {
                    //phpcs:ignore Generic.Files.LineLength.TooLong
                    throw new RuntimeException(__('Empty response when trying to retrieve specifications from Katana API.'));
                }

                $this->processSpecifications($specifications);
                $i++;
            } while ($i < $specifications['TotalPages']);
        } catch (\Throwable $e) {
            $this->log(
                $e->getMessage(),
                $importInfo,
                self::IMPORT_ERROR,
            );

            throw $e;
        }

        $this->log(
            'Specification import finished',
            $importInfo
        );
    }

    /**
     * Set the cli output
     *
     * @param OutputInterface $cliOutput
     * @return void
     */
    public function setCliOutput(OutputInterface $cliOutput): void
    {
        $this->cliOutput = $cliOutput;
    }

    /**
     * Process specification import
     *
     * @param array $specifications
     * @return void
     * @throws CouldNotSaveException
     */
    public function processSpecifications(array $specifications): void
    {
        $attributes = [];

        foreach ($specifications['Items'] as $specification) {
            $attributes[] = [
                AttributeMappingInterface::KATANA_ID => $specification['Id'],
                //phpcs:ignore Generic.Files.LineLength.TooLong
                AttributeMappingInterface::KATANA_ATTRIBUTE_CODE => !empty($specification['Code']) ? $specification['Code'] : $specification['Name'],
                AttributeMappingInterface::KATANA_ATTRIBUTE_NAME => $specification['Name'],
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE => $this->getType($specification['AttributeTypeId']),
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE_ID => $specification['AttributeTypeId'],
            ];
        }

        $this->attributeMappingRepository->insertOnDuplicate(
            $attributes,
            [
                AttributeMappingInterface::KATANA_ATTRIBUTE_CODE,
                AttributeMappingInterface::KATANA_ATTRIBUTE_NAME,
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE,
                AttributeMappingInterface::KATANA_ATTRIBUTE_TYPE_ID
            ]
        );
    }

    /**
     * Get specification type by id
     *
     * @param mixed $typeId
     * @return string
     */
    public function getType($typeId): string
    {
        if (isset($this->type[$typeId])) {
            return $this->type[$typeId];
        }

        return 'text';
    }

    /**
     * Log some information to the available output streams
     *
     * TODO: Move Output Stream handler / Logger outside this class.
     *
     * @param string $string
     * @param KatanaImportInterface $importInfo
     * @param string $level
     * @return void
     */
    private function log(string $string, KatanaImportInterface $importInfo, string $level = self::IMPORT_INFO): void
    {
        if ($level === self::IMPORT_ERROR) {
            if ($this->cliOutput instanceof OutputInterface) {
                $this->cliOutput->writeln('<error>' . $string . '</error>');
            }

            $this->logger->error(
                $string,
                ['entity_type' => $importInfo->getImportType(), 'entity_id' => $importInfo->getImportId()]
            );
        } else {
            if ($this->cliOutput instanceof OutputInterface) {
                $this->cliOutput->writeln('<info>' . $string . '</info>');
            }

            $this->logger->info(
                $string,
                ['entity_type' => $importInfo->getImportType(), 'entity_id' => $importInfo->getImportId()]
            );
        }
    }
}
