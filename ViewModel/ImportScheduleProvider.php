<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\ViewModel;

use Itonomy\Katanapim\Cron\ProductsImport;
use Itonomy\Katanapim\Cron\SpecificationsImport;
use Itonomy\Katanapim\Cron\SpecificationsLocalizationImport;
use Itonomy\Katanapim\Model\Cron\ScheduleHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ImportScheduleProvider implements ArgumentInterface
{
    /**
     * @var ScheduleHelper
     */
    private ScheduleHelper $scheduleHelper;

    /**
     * @param ScheduleHelper $scheduleHelper
     */
    public function __construct(
        ScheduleHelper $scheduleHelper
    ) {
        $this->scheduleHelper = $scheduleHelper;
    }

    /**
     * Get the latest katana pim scheduled import jobs
     *
     * @return array
     */
    public function getImportJobs(): array
    {
        $katanaImports = [
            ProductsImport::JOB_CODE,
            SpecificationsImport::JOB_CODE,
            SpecificationsLocalizationImport::JOB_CODE
        ];

        return $this->scheduleHelper->getSchedules($katanaImports, null, 20);
    }

    /**
     * Get match cronjob job code to job name
     *
     * @param string $jobCode
     * @return string
     */
    public function getImportJobName(string $jobCode): string
    {
        if ($jobCode === ProductsImport::JOB_CODE) {
            return 'Product Import';
        } elseif ($jobCode === SpecificationsImport::JOB_CODE) {
            return 'Specifications Import';
        } elseif ($jobCode === SpecificationsLocalizationImport::JOB_CODE) {
            return 'Specifications Localization/Option Import';
        } else {
            throw new \InvalidArgumentException(sprintf('Unknown cronjob name: "%s".', $jobCode));
        }
    }
}
