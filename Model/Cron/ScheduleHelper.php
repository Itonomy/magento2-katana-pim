<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Cron;

use Itonomy\Katanapim\Model\KatanaImportHelper;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ScheduleHelper
{
    /**
     * Value of seconds in one minute
     */
    private const SECONDS_IN_MINUTE = 60;

    /**
     * @var ScheduleFactory
     */
    private ScheduleFactory $scheduleFactory;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var KatanaImportHelper
     */
    private KatanaImportHelper $katanaImportHelper;

    /**
     * @param ScheduleFactory $scheduleFactory
     * @param DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     * @param KatanaImportHelper $katanaImportHelper
     */
    public function __construct(
        ScheduleFactory $scheduleFactory,
        DateTime $dateTime,
        CollectionFactory $collectionFactory,
        KatanaImportHelper $katanaImportHelper
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->dateTime = $dateTime;
        $this->collectionFactory = $collectionFactory;
        $this->katanaImportHelper = $katanaImportHelper;
    }

    /**
     * Schedule katanapim import cronjob
     *
     * @param string $jobCode
     * @return void
     * @throws LocalizedException
     */
    public function scheduleCronjob(string $jobCode): void
    {
        $schedule = $this->createScheduleModel($jobCode);
        $existing = $this->getSchedules(
            [$schedule->getJobCode()],
            [$schedule->getStatus()],
            1,
            $schedule->getScheduledAt()
        );

        if (!empty($existing)) {
            throw new LocalizedException(__('Job already scheduled.'));
        }

        $schedule->save();
        $this->katanaImportHelper->createKatanaImport(
            $schedule->getJobCode(),
            $schedule->getStatus(),
            uniqid($schedule->getJobCode() . '_')
        );
    }

    /**
     * Get existing scheduled cronjobs from cron_schedule
     *
     * @param array $jobCodes
     * @param array|null $statuses
     * @param int|null $limit
     * @param string|null $scheduledAt
     * @return array
     */
    public function getSchedules(
        array $jobCodes,
        ?array $statuses = null,
        ?int $limit = 1000,
        ?string $scheduledAt = null
    ): array {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('job_code', ['in' => $jobCodes]);
        $collection->setOrder(
            'scheduled_at',
            $collection::SORT_ORDER_DESC
        );
        $collection->setPageSize($limit);

        if ($scheduledAt !== null) {
            $collection->addFieldToFilter('scheduled_at', $scheduledAt);
        }

        if ($statuses !== null) {
            $collection->addFieldToFilter('status', ['in' => $statuses]);
        }

        return $collection->getItems() ?? [];
    }

    /**
     * Create a schedule of cron job.
     *
     * @param string $jobCode
     * @return Schedule
     */
    private function createScheduleModel(string $jobCode): Schedule
    {
        return $this->scheduleFactory->create()
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp()))
            ->setScheduledAt(date('Y-m-d H:i', $this->dateTime->gmtTimestamp() + self::SECONDS_IN_MINUTE));
    }
}
