<?php


namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

/**
 * Interface PreprocessorInterface
 */
interface PreprocessorInterface
{
    /**
     * @param array $productData
     * @return array
     */
    public function process(array $productData): array;
}
