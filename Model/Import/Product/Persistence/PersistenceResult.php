<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product\Persistence;

use Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResult\Error;

/**
 * Class for storing import results.
 */
class PersistenceResult
{
    /**
     * @var int|null
     */
    private int $created = 0;

    /**
     * @var int|null
     */
    private int $deleted = 0;

    /**
     * @var int|null
     */
    private int $updated = 0;

    /**
     * @var array|null
     */
    private array $errors = [];

    /**
     * Set count of created items
     *
     * @param int $created
     * @return void
     */
    public function setCreatedCount(int $created): void
    {
        $this->created = $created;
    }

    /**
     * Get count of created items
     *
     * @return int
     */
    public function getCreatedCount(): int
    {
        return $this->created;
    }

    /**
     * Set count of deleted items
     *
     * @param int $deleted
     * @return void
     */
    public function setDeletedCount(int $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * Get count of deleted items
     *
     * @return int
     */
    public function getDeletedCount(): int
    {
        return $this->deleted;
    }

    /**
     * Set count of updated items
     *
     * @param int $updated
     * @return void
     */
    public function setUpdatedCount(int $updated): void
    {
        $this->updated = $updated;
    }

    /**
     * Get count of updated items
     *
     * @return int
     */
    public function getUpdatedCount(): int
    {
        return $this->updated;
    }

    /**
     * Add an error
     *
     * @param Error $error
     * @return void
     */
    public function addError(Error $error): void
    {
        $this->setErrors(
            array_merge($this->getErrors(), [$error])
        );
    }

    /**
     * Set errors
     *
     * @param Error[] $errors
     * @return void
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
