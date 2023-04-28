<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

use Itonomy\Katanapim\Model\Data\Product\AttributeSetProductRegistry;

/**
 * Check if product already exists and get existing product attribute set name
 */
class AttributeSetPreprocessor implements PreprocessorInterface
{
    /**
     * @var AttributeSetProductRegistry
     */
    private AttributeSetProductRegistry $attributeSetProductRegistry;

    /**
     * @param AttributeSetProductRegistry $attributeSetProductRegistry
     */
    public function __construct(AttributeSetProductRegistry $attributeSetProductRegistry)
    {
        $this->attributeSetProductRegistry = $attributeSetProductRegistry;
    }

    /**
     * @inheritDoc
     *
     * @param array $productData
     * @return array
     */
    public function process(array $productData): array
    {
        if ($this->attributeSetProductRegistry->productExists($productData['sku'])) {
            $productData['attribute_set_code'] = $this->attributeSetProductRegistry->getAttributeSet(
                $productData['sku']
            );
        } else {
            $productData['attribute_set_code'] = 'Default';
        }
        return $productData;
    }
}
