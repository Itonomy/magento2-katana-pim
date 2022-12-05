<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResult;

class Error
{
    /**
     * @var string|null
     */
    private string $message = '';

    /**
     * @var array|null
     */
    private array $itemData = [];

    /**
     * Set error message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set data on the related items
     *
     * @param array $itemData
     * @return $this
     */
    public function setItemData(array $itemData): self
    {
        $this->itemData = $itemData;
        return $this;
    }

    /**
     * Get data on the items related to the error
     *
     * @return array
     */
    public function getitemData(): array
    {
        return $this->itemData;
    }
}
