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
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getImportId(): ?int
    {
        return $this->getData(self::IMPORT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setImportId(?int $importId): void
    {
        $this->setData(self::IMPORT_ID, $importId);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType(): string
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setEntityType(string $entityType): void
    {
        $this->setData(self::ENTITY_TYPE, $entityType);
    }

    /**
     * @inheritDoc
     */
    public function getStartTime(): ?string
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * @inheritDoc
     */
    public function setStartTime(?string $startTime): void
    {
        $this->setData(self::START_TIME, $startTime);
    }

    /**
     * @inheritDoc
     */
    public function getFinishTime(): ?string
    {
        return $this->getData(self::FINISH_TIME);
    }

    /**
     * @inheritDoc
     */
    public function setFinishTime(?string $finishTime): void
    {
        $this->setData(self::FINISH_TIME, $finishTime);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): void
    {
        $this->setData(self::STATUS, $status);
    }
}
