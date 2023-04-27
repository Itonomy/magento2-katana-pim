<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product;

use Magento\Framework\App\ResourceConnection;

/**
 * Class responsible for storing product-sku > attribute set name relationship
 */
class ProductRegistry
{
    /**
     * @var array
     */
    private array $skus = [];

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get products attribute sets by skus
     *
     * @param array $skus
     * @return void
     */
    public function fetchProducts(array $skus)
    {
        $this->skus = $this->getProductsAttributeSetBySkus($skus);
    }

    /**
     * Get product attribute set name
     *
     * @param string $sku
     * @return string
     */
    public function getAttributeSet(string $sku): string
    {
        return $this->skus[$sku];
    }

    /**
     * Check if product exists in Magento
     *
     * @param string $sku
     * @return bool
     */
    public function productExists(string $sku): bool
    {
        if (array_key_exists($sku, $this->skus)) {
            return true;
        }

        return false;
    }

    /**
     * Get product attribute sets by their sku
     *
     * @param  array $productSkuList
     * @return array
     */
    private function getProductsAttributeSetBySkus(array $productSkuList): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            ['sku', 'attribute_set_id']
        )->joinLeft(
            ['eas' => $this->resourceConnection->getTableName('eav_attribute_set')],
            'eas.attribute_set_id = cpe.attribute_set_id',
            ['attribute_set_name']
        )->where(
            'sku IN (?)',
            $productSkuList
        );

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['sku']] = $row['attribute_set_name'];
        }
        return $result;
    }
}
