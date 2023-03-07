<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataValidator;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\UrlRewrite\Model\Storage\DbStorage;

class UrlKeyValidator implements ValidatorInterface
{
    /**
     * @var Product
     */
    private Product $productResource;

    /**
     * @var DbStorage
     */
    private DbStorage $storage;

    /**
     * @param Product $productResource
     * @param DbStorage $storage
     */
    public function __construct(
        Product $productResource,
        DbStorage $storage
    ) {
        $this->productResource = $productResource;
        $this->storage = $storage;
    }

    /**
     * @param array $productData
     * @return bool
     */
    public function validate(array &$productData): bool
    {
        $sku = $productData['sku'];
        $productId = $this->productResource->getProductsIdsBySkus([$sku]);
        $rewrite = $this->storage->findOneByData(
            ['entity_type' => 'product', 'entity_id' => $productId, 'store_id' => $productData['_store']]
        );

        if ($rewrite) {
            return false;
        }

        unset($productData['url_key']);
        return true;
    }
}
