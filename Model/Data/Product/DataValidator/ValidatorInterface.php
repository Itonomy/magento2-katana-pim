<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataValidator;

/**
 * Interface PreprocessorInterface
 */
interface ValidatorInterface
{
    /**
     * Validate the api data before saving
     *
     * @param array $productData
     * @return bool
     */
    public function validate(array &$productData): bool;
}
