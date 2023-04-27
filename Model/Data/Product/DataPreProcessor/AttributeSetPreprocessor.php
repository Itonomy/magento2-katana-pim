<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

use Itonomy\Katanapim\Model\Data\Product\ProductRegistry;

/**
 * Check if product already exists and get existing product attribute set name
 */
class AttributeSetPreprocessor implements PreprocessorInterface
{
    /**
     * @var ProductRegistry
     */
    private ProductRegistry $productRegistry;

    /**
     * @param ProductRegistry $productRegistry
     */
    public function __construct(ProductRegistry $productRegistry)
    {
        $this->productRegistry = $productRegistry;
    }

    /**
     * @inheritDoc
     *
     * @param array $productData
     * @return array
     */
    public function process(array $productData): array
    {
        if ($this->productRegistry->productExists($productData['sku'])) {
            $productData['attribute_set_code'] = $this->productRegistry->getAttributeSet($productData['sku']);
        } else {
            $productData['attribute_set_code'] = 'Default';
        }
        return $productData;
    }
}
