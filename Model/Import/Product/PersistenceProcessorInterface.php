<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product;

interface PersistenceProcessorInterface
{
    /**
     * Save product data
     *
     * @param array $data
     */
    public function save(array $data): void;
}
