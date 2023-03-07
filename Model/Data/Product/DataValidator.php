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
    private array $defaultValidators;

    /**
     * @var ValidatorInterface[]
     */
    private array $scopeValidators;

    /**
     * @var array
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
     * Validate data.
     *
     * @param array $data
     * @param bool $scope
     * @return array
     */
    public function execute(array &$data, bool $scope = false): array
    {
        if (!empty($this->validators) && empty($this->scopeValidators) && empty($this->defaultValidators)) {
            $this->setValidators();
        }

        foreach ($data as $productId => $productData) {
            if ($scope) {
                foreach ($this->scopeValidators as $validator) {
                    $valid = $validator->validate($productData);
                    if (!$valid) {
                        unset($data[$productId]);
                    } else {
                        $data[$productId] = $productData;
                    }
                }
            } else {
                foreach ($this->defaultValidators as $validator) {
                    $valid = $validator->validate($productData);
                    if (!$valid) {
                        unset($data[$productId]);
                    } else {
                        $data[$productId] = $productData;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @return void
     */
    private function setValidators(): void
    {
        $defaultValidators = array_filter(
            $this->validators,
            function ($item) {
                return !isset($item['scope']);
            }
        );
        $this->defaultValidators = array_column($defaultValidators, 'object');

        $scopeValidators = array_filter(
            $this->validators,
            function ($item) {
                return isset($item['scope']) && (bool) $item['scope'];
            }
        );
        $this->scopeValidators = array_column($scopeValidators, 'object');
    }
}
