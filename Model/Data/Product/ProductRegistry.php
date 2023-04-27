<?php

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
     * @param $skus
     * @return void
     */
    public function fetchProducts($skus)
    {
        $this->skus = $this->getProductsAttributeSetBySkus($skus);
    }

    /**
     * Get product attribute set name
     *
     * @param $sku
     * @return string
     */
    public function getAttributeSet($sku): string
    {
        return $this->skus[$sku];
    }

    /**
     * Check if product exists in Magento
     *
     * @param $sku
     * @return bool
     */
    public function productExists($sku): bool
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
            $result[$this->getResultKey($row['sku'], $productSkuList)] = $row['attribute_set_name'];
        }
        return $result;
    }

    /**
     * Return correct key for result array in getProductsAttributeSetBySkus
     * Allows for different case sku to be passed in search array
     * with original cased sku to be passed back in result array
     *
     * @param string $sku
     * @param array $productSkuList
     * @return string
     */
    private function getResultKey(string $sku, array $productSkuList): string
    {
        $key = array_search(strtolower($sku), array_map('strtolower', $productSkuList));
        if ($key !== false) {
            $sku = $productSkuList[$key];
        }
        return $sku;
    }
}
