<?php


namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

/**
 * Interface PreprocessorInterface
 */
interface PreprocessorInterface
{
    /**
     * Manipulate the api data before saving
     *
     * @param array $productData
     * @return array
     */
    public function process(array $productData): array;

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors(): array;
}
