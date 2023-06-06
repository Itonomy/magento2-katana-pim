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
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getImportId(): ?string
    {
        return $this->getData(self::IMPORT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setImportId(?string $importId): KatanaImportInterface
    {
        return $this->setData(self::IMPORT_ID, $importId);
    }

    /**
     * @inheritDoc
     */
    public function getImportType(): string
    {
        return $this->getData(self::IMPORT_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setImportType(string $importType): KatanaImportInterface
    {
        return $this->setData(self::IMPORT_TYPE, $importType);
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
    public function setStartTime(?string $startTime): KatanaImportInterface
    {
        return $this->setData(self::START_TIME, $startTime);
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
    public function setFinishTime(?string $finishTime): KatanaImportInterface
    {
        return $this->setData(self::FINISH_TIME, $finishTime);
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
    public function setStatus(string $status): KatanaImportInterface
    {
        return $this->setData(self::STATUS, $status);
    }
}
