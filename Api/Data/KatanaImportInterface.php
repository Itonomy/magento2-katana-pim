<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface KatanaImportInterface
{
    /**
     * String constants for property names
     */
    public const IMPORT_ID = "import_id";
    public const IMPORT_TYPE = "import_type";
    public const START_TIME = "start_time";
    public const FINISH_TIME = "finish_time";
    public const STATUS = "status";

    /**
     * Import statuses
     */
    public const STATUS_PENDING = "pending";
    public const STATUS_RUNNING = "running";
    public const STATUS_COMPLETE = "complete";
    public const STATUS_ERROR = "error";

    /**
     * Import types
     */
    public const PRODUCT_IMPORT_TYPE = "product";
    public const SPECIFICATION_IMPORT_TYPE = "specification";
    public const SPECIFICATION_GROUP_IMPORT_TYPE = "specification_group";
    public const SPECIIFICATION_LOCALIZATION_IMPORT_TYPE = "specification_localization";


    /**
     * Getter for ImportId.
     *
     * @return string
     */
    public function getImportId(): ?string;

    /**
     * Setter for ImportId.
     *
     * @param string|null $importId
     *
     * @return $this
     */
    public function setImportId(?string $importId): KatanaImportInterface;

    /**
     * Getter for EntityType.
     *
     * @return string|null
     */
    public function getImportType(): ?string;

    /**
     * Setter for EntityType.
     *
     * @param string $entityType
     *
     * @return $this
     */
    public function setImportType(string $importType): KatanaImportInterface;

    /**
     * Getter for StartTime.
     *
     * @return string|null
     */
    public function getStartTime(): ?string;

    /**
     * Setter for StartTime.
     *
     * @param string|null $startTime
     *
     * @return $this
     */
    public function setStartTime(?string $startTime): KatanaImportInterface;

    /**
     * Getter for FinishTime.
     *
     * @return string|null
     */
    public function getFinishTime(): ?string;

    /**
     * Setter for FinishTime.
     *
     * @param string|null $finishTime
     *
     * @return $this
     */
    public function setFinishTime(?string $finishTime): KatanaImportInterface;

    /**
     * Getter for Status.
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Setter for Status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status): KatanaImportInterface;
}
