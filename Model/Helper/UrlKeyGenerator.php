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
     * @param string|int $sku
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function generateUrlKey($sku): string
    {
        return str_replace(' ', '-', strtolower((string)$sku));
    }
}
