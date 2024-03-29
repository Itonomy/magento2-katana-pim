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
     * @param array $productsData
     * @return void
     */
    public function validate(array &$productsData): void;
}
