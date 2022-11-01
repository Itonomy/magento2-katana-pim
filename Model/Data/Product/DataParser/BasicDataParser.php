<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataParser;

class BasicDataParser implements DataParserInterface
{
    public const KATANA_PRODUCT_TYPE = 5;
    public const PRODUCT_TYPE = 'simple';
    public const VISIBILITY = 'Catalog, Search';

    protected const DATA_MAP = [
//Basic Data
        'sku' => ['TextFieldsModel','Sku'],
        'name' => ['TextFieldsModel','Name'],
        'product_online' => ['Settings', 'Published'],
//Seo Data
        'description' => ['TextFieldsModel', 'FullDescription'],
        'short_description' => ['TextFieldsModel', 'ShortDescription'],
        'meta_title' => ['TextFieldsModel', 'MetaTitle'],
        'meta_keywords' => ['TextFieldsModel', 'MetaKeywords'],
        'meta_description' => ['TextFieldsModel', 'MetaDescription'],
//Price Data
        'price' => ['Prices','CurrentPriceBookItem','Price'],
//Images Data
        'images' => ['Collections','Images'],
//Inventory Data
        'qty' => ['Stock','TotalStock'],
//Categories Data
        'categories' => ['Collections', 'Categories'],
//Misc
        'katana_pim_id' => ['Id'],
        'katana_attachments' => ['Collections','Attachments'],
    ];

    public array $parsedData = [];

    /**
     * @param array $data
     * @return array
     */
    public function parse(array $data): array
    {
        if ($data['ProductType'] != $this::KATANA_PRODUCT_TYPE) {
            return [];
        }

        return $this->parseData($data, self::DATA_MAP);
    }

    /**
     * @param array $item
     * @param array $attributesMapping
     * @return array
     */
    protected function parseData(array $item, array $attributesMapping): array
    {
        $output = [];

        $output['product_type'] = self::PRODUCT_TYPE;
        $output['parent_id'] = $item['ParentId'];

        if ($output['parent_id']) {
            $output['data'] = $item;
        }

        foreach ($attributesMapping as $attributeCode => $katanaKey) {
            $output[$attributeCode] = $this->findValue($item, $katanaKey);

            if ($attributeCode === 'sku' && empty($output[$attributeCode])) {
                $output[$attributeCode] = $item['Id'];
            }
        }

        return $output;
    }

    /**
     * @param $item
     * @param $attributeLocation
     * @return array|mixed
     */
    protected function findValue($item, $attributeLocation)
    {
        $current = $item;

        foreach ($attributeLocation as $key) {
            $current = $current[$key] ?? null;
        }

        return $current;
    }

    /**
     * @param array $parsedData
     * @return $this
     */
    public function setParsedData(array $parsedData): BasicDataParser
    {
        $this->parsedData = $parsedData;
        return $this;
    }
}
