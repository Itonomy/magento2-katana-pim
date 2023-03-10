<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataValidator;

use Magento\Catalog\Model\Product\Url;
use Itonomy\Katanapim\Model\Logger;

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
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param Url $productUrl
     * @param Logger $logger
     */
    public function __construct(
        Url $productUrl,
        Logger $logger
    ) {
        $this->productUrl = $productUrl;
        $this->logger = $logger;
    }

    /**
     * Check if url_key exists from API or generate one from SKU, if still not available skip the row
     *
     * @param array $productData
     * @return bool
     */
    public function validate(array &$productData): bool
    {
        $urlKey = null;
        if (array_key_exists(self::URL_KEY, $productData)) {
            $urlKey = $this->getUrlKey($productData);
        }

        if ($urlKey) {
            $productData[self::URL_KEY] = $urlKey;
        } else {
            if (!empty($productData[self::COL_SKU])) {
                $this->logger->info(
                    'Skipping product, url_key doesn\'t exist for product with SKU: ',
                    [$productData[self::COL_SKU]]
                );
            } else {
                $this->logger->info('Skipping product, url_key doesn\'t exist for product');
            }

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

        if (!empty($productData[self::COL_SKU])) {
            return $this->productUrl->formatUrlKey($productData[self::COL_SKU]);
        }

        return '';
    }
}
