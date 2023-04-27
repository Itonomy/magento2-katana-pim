<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product;

use Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\PreprocessorInterface;

/**
 * Class DataParser
 */
class DataPreprocessor
{
    /**
     * @var PreprocessorInterface[]
     */
    private array $processors;

    /**
     * @var AttributeSetProductRegistry
     */
    private AttributeSetProductRegistry $attributeSetProductRegistry;

    /**
     * DataPreprocessor constructor.
     *
     * @param AttributeSetProductRegistry $attributeSetProductRegistry
     * @param array $processors
     */
    public function __construct(
        AttributeSetProductRegistry $attributeSetProductRegistry,
        array $processors
    ) {
        $this->processors = $processors;
        $this->attributeSetProductRegistry = $attributeSetProductRegistry;
    }

    /**
     * Pre-parse and organise data.
     *
     * @param array $data
     * @return array
     */
    public function process(array $data): array
    {
        $skus = \array_column($data, 'sku');
        $this->attributeSetProductRegistry->fetchProducts($skus);
        foreach ($data as $productId => &$productData) {
            //TODO: Generate sku for configurable products
            if (!isset($productData['sku'])) {
                unset($data[$productId]);
                continue;
            }

            foreach ($this->processors as $processor) {
                $productData = $processor->process($productData);
            }
        }

        return $data;
    }
}
