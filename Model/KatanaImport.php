<?php

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Model\ResourceModel\KatanaImport as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class KatanaImport extends AbstractModel implements KatanaImportInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'katanapim_import_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Getter for EntityType.
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * Setter for EntityType.
     *
     * @param string $entityType
     *
     * @return void
     */
    public function setEntityType(string $entityType): void
    {
        $this->setData(self::ENTITY_TYPE, $entityType);
    }

    /**
     * Getter for StartTime.
     *
     * @return string|null
     */
    public function getStartTime(): ?string
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * Setter for StartTime.
     *
     * @param string|null $startTime
     *
     * @return void
     */
    public function setStartTime(?string $startTime): void
    {
        $this->setData(self::START_TIME, $startTime);
    }

    /**
     * Getter for FinishTime.
     *
     * @return string|null
     */
    public function getFinishTime(): ?string
    {
        return $this->getData(self::FINISH_TIME);
    }

    /**
     * Setter for FinishTime.
     *
     * @param string|null $finishTime
     *
     * @return void
     */
    public function setFinishTime(?string $finishTime): void
    {
        $this->setData(self::FINISH_TIME, $finishTime);
    }

    /**
     * Getter for Status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Setter for Status.
     *
     * @param string $status
     *
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->setData(self::STATUS, $status);
    }
}
