<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Helper;

/**
 * Url Key Generator utility class
 */
class UrlKeyGenerator
{
    /**
     * Generate url key
     *
     * @param array $productData
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public function generateUrlKey(array $productData): string
    {
        $urlKey = $productData['TextFieldsModel']['Slug'] ?? null;

        if (!$urlKey) {
            $urlKey = str_replace(' ', '-', strtolower((string) $productData['TextFieldsModel']['Sku'] ));
        }

        return $urlKey;
    }
}
