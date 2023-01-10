<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataParser;

use Itonomy\Katanapim\Model\ResourceModel\AttributeMapping\CollectionFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;

class SpecificationsDataParser implements DataParserInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $mappingCollectionFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private ProductAttributeRepositoryInterface $productAttributeRepository;

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private ProductAttributeOptionManagementInterface $attributeOptionManagement;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    private AttributeOptionInterfaceFactory $attributeOptionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    private AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory;

    /**
     * @var array
     */
    private array $attributeMapping;

    /**
     * @var array
     */
    public array $parsedData = [];

    /**
     * SpecificationsDataParser constructor.
     *
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param CollectionFactory $mappingCollectionFactory
     * @param ProductAttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionInterfaceFactory $attributeOptionFactory
     * @param StoreManagerInterface $storeManager
     * @param AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        CollectionFactory $mappingCollectionFactory,
        ProductAttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionInterfaceFactory $attributeOptionFactory,
        StoreManagerInterface $storeManager,
        AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->mappingCollectionFactory = $mappingCollectionFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->storeManager = $storeManager;
        $this->attributeOptionLabelInterfaceFactory = $attributeOptionLabelInterfaceFactory;
        $this->attributeMapping = [];
    }

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function parse(array $data): array
    {
        $attributesMapping = $this->getSortedCustomAttributeMapping();

        return $this->parseData($data, $attributesMapping);
    }

    /**
     * Parse data
     *
     * @param array $item
     * @param array $attributesMapping
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Exception
     */
    private function parseData(array $item, array $attributesMapping): array
    {
        if (!empty($this->parsedData) && !isset($this->parsedData[$item['Id']])) {
            return [];
        }

        $output = [];
        $productSpecs = $this->getSortedSpecifications($item);

        foreach ($attributesMapping as $magentoCode => $attributeMapping) {
            $katanaCode = $attributeMapping['katana_attribute_code'];
            $value = $productSpecs[$katanaCode]['value'] ?? null;

            if ($value !== null) {
                $value = \trim($value);
                $attribute = $this->productAttributeRepository->get($magentoCode);

                if ($attribute->getFrontendInput() === 'select') {
                    if ($attributeMapping['katana_attribute_type'] !== 'select') {
                        throw new \RuntimeException(sprintf(
                            "Magento select type attribute mapped to a KatanaPim non-select type attribute.
                            Magento attribute code: %s Katana code: %s",
                            $magentoCode,
                            $katanaCode
                        ));
                    }

                    if (!$this->isSelectAttributeValueExists($value, $attribute)) {
                        $this->createNewOption($value, $attribute);
                    }
                }
            }

            $output[$magentoCode] = $value;
        }

        return $output;
    }

    /**
     * Get custom attribute mapping sorted by magento attribute codes
     *
     * @return array
     */
    private function getSortedCustomAttributeMapping(): array
    {
        if (empty($this->attributeMapping)) {
            $collection = $this->mappingCollectionFactory->create();
            $collection->addFieldToFilter('magento_attribute_code', ['notnull' => true]);
            $collection->addFieldToFilter('magento_attribute_code', ['neq' => '']);
            $items = $collection->toArray()['items'] ?? [];

            foreach ($items as $item) {
                $this->attributeMapping[$item['magento_attribute_code']] = $item;
            }
        }

        return $this->attributeMapping;
    }

    /**
     * Get sorted specifications
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function getSortedSpecifications(array $data): array
    {
        $sorted = [];
        try {
            foreach ($data['Collections']['Specs'] as $specification) {
                $sorted[$specification['Code'] ?? $specification['Name']] = [
                    'value' => $specification['OptionName']
                ];
            }
        } catch (\Throwable $e) {
            throw new \Exception(
                'Missing specification value ' . $e->getMessage()
            );
        }

        return $sorted;
    }

    /**
     * Check if the attribute option already exists
     *
     * @param string $value
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    private function isSelectAttributeValueExists(string $value, ProductAttributeInterface $attribute): bool
    {
        $options = $attribute->getOptions();

        foreach ($options as $option) {
            if ($option->getLabel() === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create new attribute option
     *
     * @param string $value
     * @param ProductAttributeInterface $attribute
     * @throws InputException
     * @throws StateException
     */
    private function createNewOption(string $value, ProductAttributeInterface $attribute): void
    {
        $option = $this->attributeOptionFactory->create();
        $option->setValue($value);
        $option->setLabel($value);

        $defaultStore = $this->storeManager->getDefaultStoreView();

        if ($defaultStore === null) {
            throw new \LogicException(
                'Default Store View not found while setting product attribute option value.'
            );
        }

        $optionLabel = $this->attributeOptionLabelInterfaceFactory->create();
        $optionLabel->setStoreId($defaultStore->getId())->setLabel($value);
        $option->setStoreLabels([$optionLabel]);

        $attributeCode = $attribute->getAttributeCode();
        $this->attributeOptionManagement->add($attributeCode, $option);
    }

    /**
     * @inheritDoc
     *
     * @param array $parsedData
     * @return DataParserInterface
     */
    public function setParsedData(array $parsedData): DataParserInterface
    {
        $this->parsedData = $parsedData;

        return $this;
    }
}
