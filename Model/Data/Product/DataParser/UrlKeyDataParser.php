<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataParser;

use Magento\Catalog\Model\Product\Url;

class UrlKeyDataParser implements DataParserInterface
{
    /**
     * @var Url
     */
    private Url $productUrl;

    /**
     * @var array
     */
    public array $parsedData = [];

    /**
     * @param Url $productUrl
     */
    public function __construct(Url $productUrl)
    {
        $this->productUrl = $productUrl;
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return array
     */
    public function parse(array $data): array
    {
        return $this->parseData($data);
    }

    /**
     * Parse data
     *
     * @param array $item
     * @return array
     */
    protected function parseData(array $item): array
    {
        $output = [];

        if (!empty($item['TextFieldsModel']['Slug'])) {
            $rawUrlKey = $item['TextFieldsModel']['Slug'];
        } elseif (!empty($item['TextFieldsModel']['Sku'])) {
            $rawUrlKey = $item['TextFieldsModel']['Sku'];
        } else {
            $rawUrlKey = (string)$item['Id'];
        }

        $output['url_key'] = $this->productUrl->formatUrlKey($rawUrlKey);

        return $output;
    }

    /**
     * @inheritDoc
     *
     * @param array $parsedData
     * @return UrlKeyDataParser
     */
    public function setParsedData(array $parsedData): UrlKeyDataParser
    {
        $this->parsedData = $parsedData;

        return $this;
    }
}
