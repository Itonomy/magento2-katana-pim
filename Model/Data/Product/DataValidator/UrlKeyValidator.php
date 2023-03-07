<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataValidator;

use Magento\Catalog\Model\Product\Url;

class UrlKeyValidator implements ValidatorInterface
{
    /**
     * Url key attribute code
     */
    public const URL_KEY = 'url_key';

    /**
     * Column product sku.
     */
    public const COL_SKU = 'sku';

    /**
     * @var Url
     */
    private Url $productUrl;

    /**
     * @param Url $productUrl
     */
    public function __construct(Url $productUrl)
    {
        $this->productUrl = $productUrl;
    }

    /**
     * Check if url_key exists from API or generate one from SKU, if still not available skip the row
     *
     * @param array $productData
     * @return bool
     */
    public function validate(array &$productData): bool
    {
        $urlKey = $productData['url_key'];
        if (!$urlKey) {
            $urlKey = $this->getUrlKey($productData);
        }

        if ($urlKey) {
            $productData['url_key'] = $urlKey;
        } else {
            return false;
        }

        return true;
    }

    /**
     * Retrieve url key from provided product data.
     *
     * @param array $productData
     * @return string
     */
    protected function getUrlKey(array $productData): string
    {
        if (!empty($productData[self::URL_KEY])) {
            $urlKey = (string) $productData[self::URL_KEY];
            return $this->productUrl->formatUrlKey($urlKey);
        }

        if (!empty($productData[self::COL_SKU])
            && (array_key_exists(self::URL_KEY, $productData))) {
            return $this->productUrl->formatUrlKey($productData[self::COL_SKU]);
        }

        return '';
    }
}
