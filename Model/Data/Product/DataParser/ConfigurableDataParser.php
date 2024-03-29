<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataParser;

use Itonomy\Katanapim\Model\AttributeSetToAttributeRepository;
use Magento\Framework\Exception\LocalizedException;

class ConfigurableDataParser extends BasicDataParser
{
    /**
     * Basic Product Type Id in KatanaPIM
     */
    public const KATANA_PRODUCT_TYPE = 30;

    /**
     * Basic Product Type
     */
    public const PRODUCT_TYPE = 'configurable';

    /**
     * Basic Product Visibility
     */
    public const VISIBILITY = 'Catalog, Search';

    /**
     * @var AttributeSetToAttributeRepository
     */
    private AttributeSetToAttributeRepository $setToAttributeRepository;

    /**
     * @var array
     */
    private array $configurableData = [];

    /**
     * ConfigurableDataParser constructor.
     *
     * @param AttributeSetToAttributeRepository $setToAttributeRepository
     */
    public function __construct(
        AttributeSetToAttributeRepository $setToAttributeRepository
    ) {
        $this->setToAttributeRepository = $setToAttributeRepository;
    }

    /**
     * @inheritDoc
     *
     * @param array $item
     * @param array $attributesMapping
     * @return array
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function parseData(array $item, array $attributesMapping): array
    {
        $output = parent::parseData($item, $attributesMapping);

        $output['product_type'] = self::PRODUCT_TYPE;
        $output['visibility'] = self::VISIBILITY;

        if (empty($item['Collections']['SpecificationGroups']) || empty($this->configurableData[$item['Id']])) {
            return [];
        }

        $configurableAttributes = [];

        foreach ($item['Collections']['SpecificationGroups'] as $specificationGroups) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $configurableAttributes = array_merge(
                $configurableAttributes,
                $this->setToAttributeRepository->getConfigurableVariationsCodes($specificationGroups['Id'])
            );
        }

        $rowConfigurableVariations = [];

        foreach ($this->configurableData[$item['Id']] as $childProduct) {
            foreach ($childProduct['Collections']['Specs'] as $spec) {
                if (!isset($configurableAttributes[$spec['Code'] ?? $spec['Name']])) {
                    continue;
                }

                $sku = $childProduct['TextFieldsModel']['Sku'] ?? $childProduct['Id'];
                //phpcs:ignore Generic.Files.LineLength.TooLong
                $rowConfigurableVariations[$sku][$configurableAttributes[$spec['Code'] ?? $spec['Name']]] = $spec['OptionName'];
            }
        }

        if (empty($rowConfigurableVariations)) {
            return [];
        }

        $configurableVariations = [];

        foreach ($rowConfigurableVariations as $child => $attribute) {
            $string = 'sku=' . $child;

            foreach ($attribute as $code => $value) {
                $string .= ',' . $code . '=' . $value;
            }

            $configurableVariations[] = $string;
        }

        $configurableVariationLabels = [];

        foreach ($configurableAttributes as $attribute) {
            $configurableVariationLabels[] = $attribute . '=' . ucfirst($attribute);
        }

        $output['configurable_variations'] = implode('|', $configurableVariations);
        $output['configurable_variation_labels'] = implode('|', $configurableVariationLabels);

        return $output;
    }

    /**
     * @inheritDoc
     *
     * @param array $parsedData
     * @return $this
     */
    public function setParsedData(array $parsedData): BasicDataParser
    {
        if (!empty($parsedData)) {
            foreach ($parsedData as $item) {
                if (empty($item['parent_id'])) {
                    continue;
                }

                $this->configurableData[$item['parent_id']][$item['sku']] = $item['data'];
            }
        }

        $this->parsedData = $parsedData;
        return $this;
    }
}
