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

    public function __construct(
        array $processors
    ) {
        $this->processors = $processors;
    }

    /**
     * Pre-parse and organise data.
     *
     * @param array $data
     * @return array
     */
    public function process(array $data): array
    {
        foreach ($data as $productId => &$productData) {
            //TODO: Generate sku for configurable products
            if ($productData['sku'] === null) {
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
