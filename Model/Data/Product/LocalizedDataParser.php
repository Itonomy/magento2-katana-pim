<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product;

use Itonomy\Katanapim\Model\Data\Product\DataParser\BasicDataParser;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;

class LocalizedDataParser
{
    private const ATTRIBUTES_MAP = [
        'name' => 'Name',
        'description' => 'FullDescription',
        'short_description' => 'ShortDescription',
        'url_key' => 'Slug'
    ];

    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepository;

    /**
     * LocalizedDataParser constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository
    ) {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Parse data coming from katana PIM API.
     *
     * @param array $data
     * @param int $storeViewId
     * @param string $languageCode
     * @return array
     * @throws NoSuchEntityException
     */
    public function parse(array $data, int $storeViewId, string $languageCode): array
    {
        $output = [];

        $store = $this->storeRepository->getById($storeViewId);
        $storeViewCode = $store->getCode();

        foreach ($data as $datum) {
            if ($datum['ProductType'] != BasicDataParser::KATANA_PRODUCT_TYPE) {
                continue;
            }

            $itemData = $this->parseData($datum, $languageCode);

            if (empty(array_filter($itemData))) {
                continue;
            }

            $itemData['sku'] = empty($datum['TextFieldsModel']['Sku']) ?
                $datum['Id'] :
                $datum['TextFieldsModel']['Sku'];
            $itemData['store_view_code'] = $storeViewCode;
            $itemData['_store'] = $storeViewId;

            $output[$datum['Id']] = $itemData;
        }

        return $output;
    }

    /**
     * Find localized attribute values which match a certain language
     *
     * TODO: Make this more efficient (order data before looking for values..)
     *
     * @param array $datum
     * @param string $languageCode
     * @return array
     */
    private function parseData(array $datum, string $languageCode): array
    {
        $output = [];

        foreach (self::ATTRIBUTES_MAP as $attributeCode => $katanaKey) {
            $output[$attributeCode] = null;

            foreach ($datum['LocalizedProperties'] as $localizedProperty) {
                if ($localizedProperty['LocaleKey'] === $katanaKey
                    && $localizedProperty['LanguageCulture'] === $languageCode) {
                    if (!empty($localizedProperty['LocaleValue'])) {
                        $output[$attributeCode] = $localizedProperty['LocaleValue'];
                    }

                    break;
                }
            }
        }

        return $output;
    }
}
