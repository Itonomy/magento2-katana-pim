<?php

namespace Itonomy\Katanapim\Model\Data\Product\DataValidator;

class UrlKeyValidator implements ValidatorInterface
{
    /**
     * @param array $productData
     * @return bool
     */
    public function validate(array $productData): bool
    {
        return true;
    }
}
