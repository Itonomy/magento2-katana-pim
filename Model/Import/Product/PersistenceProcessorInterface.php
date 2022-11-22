<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product;

use Itonomy\Katanapim\Model\Import\Product\Persistence\PersistenceResult;

interface PersistenceProcessorInterface
{
    /**
     * Save product data
     *
     * @param array $data
     */
    public function save(array $data): PersistenceResult;
}
