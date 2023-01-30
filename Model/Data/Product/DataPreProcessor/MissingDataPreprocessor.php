<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

use Itonomy\Katanapim\Model\Helper\UrlKeyGenerator;

class MissingDataPreprocessor implements PreprocessorInterface
{
    /**
     * @var UrlKeyGenerator
     */
    private UrlKeyGenerator $urlKeyGenerator;

    /**
     * @param UrlKeyGenerator $urlKeyGenerator
     */
    public function __construct(UrlKeyGenerator $urlKeyGenerator)
    {
        $this->urlKeyGenerator = $urlKeyGenerator;
    }

    /**
     * @inheritDoc
     *
     * @param array $productData
     * @return array
     */
    public function process(array $productData): array
    {
        if (!isset($productData['visibility'])) {
            $productData['visibility'] = 'Catalog, Search'; //Catalog, Search | Not Visible Individually
        }

        if (!empty($productData['parent_id']) && $productData['product_type'] === 'simple') {
            $productData['visibility'] = 'Not Visible Individually';
        }

        $productData['is_decimal_divided'] = 0;
        $productData['attribute_set_code'] = 'Default';
        //$productData['out_of_stock_qty'] = 0;
        //$productData['use_config_min_qty'] = 1;
        //$productData['is_qty_decimal'] = 0;
        //$productData['allow_backorders'] = 0;
        //$productData['use_config_backorders'] = 1;
        //$productData['min_cart_qty'] = 1;
        $productData['use_config_min_sale_qty'] = 0;
        //$productData['max_cart_qty'] = 0;
        //$productData['use_config_max_sale_qty'] = 1;
        $productData['is_in_stock'] = $productData['qty'] > 0 ? '1' : '0';
        //$productData['notify_on_stock_below'] = 1;
        //$productData['use_config_notify_stock_qty'] = 0;
        //$productData['manage_stock'] = 1;
        //$productData['use_config_manage_stock'] = 1;
        //$productData['use_config_qty_increments'] = 1;
        //$productData['qty_increments'] = 0;
        //$productData['use_config_enable_qty_inc'] = 1;
        //$productData['enable_qty_increments'] = 0;
        //$productData['is_decimal_divided'] = 0;

        if (!isset($productData['configurable_variations'])) {
            $productData['configurable_variations'] = '';
        }

        if (!isset($productData['configurable_variation_labels'])) {
            $productData['configurable_variation_labels'] = '';
        }

        if (empty($productData['url_key'])) {
            $productData['url_key'] = $this->urlKeyGenerator->generateUrlKey($productData['sku']);
        }

        if ($productData['qty'] > 99999999) {
            $productData['qty'] = 99999999;
        }

        $productData['status'] = 1;

        unset($productData['parent_id'], $productData['data']);

        return $productData;
    }
}
