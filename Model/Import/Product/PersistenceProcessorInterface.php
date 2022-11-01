<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Import\Product;

interface PersistenceProcessorInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function save(array $data);
}
