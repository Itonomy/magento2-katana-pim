<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface KatanaImportInterface
{
    /**
     * String constants for property names
     */
    public const IMPORT_ID = "import_id";
    public const ENTITY_TYPE = "entity_type";
    public const START_TIME = "start_time";
    public const FINISH_TIME = "finish_time";
    public const STATUS = "status";
    public const STATUS_PENDING = "pending";
    public const STATUS_RUNNING = "running";
    public const STATUS_COMPLETE = "complete";
    public const STATUS_ERROR = "error";


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
     * @return void
     */
    public function setImportId(?string $importId): void;

    /**
     * Getter for EntityType.
     *
     * @return string|null
     */
    public function getEntityType(): ?string;

    /**
     * Setter for EntityType.
     *
     * @param string $entityType
     *
     * @return void
     */
    public function setEntityType(string $entityType): void;

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
     * @return void
     */
    public function setStartTime(?string $startTime): void;

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
     * @return void
     */
    public function setFinishTime(?string $finishTime): void;

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
     * @return void
     */
    public function setStatus(string $status): void;
}
