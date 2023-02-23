<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product;

use Itonomy\Katanapim\Model\Data\Product\DataValidator\ValidatorInterface;

/**
 * Class DataValidator
 */
class DataValidator
{
    /**
     * @var ValidatorInterface[]
     */
    private array $validators;

    /**
     * DataValidator constructor.
     *
     * @param array $validators
     */
    public function __construct(
        array $validators
    ) {
        $this->validators = $validators;
    }

    /**
     * Pre-parse and organise data.
     *
     * @param array $data
     * @return array
     */
    public function execute(array $data): array
    {
        foreach ($data as $productId => &$productData) {
            $valid = true;
            foreach ($this->validators as $validator) {
                $valid = $validator->validate($productData);
            }

            if (!$valid) {
                unset($data[$productId]);
            }
        }

        return $data;
    }
}
