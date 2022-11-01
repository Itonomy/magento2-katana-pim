<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataParser;

use Itonomy\Katanapim\Model\ResourceModel\AttributeMapping\CollectionFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;

class SpecificationsDataParser implements DataParserInterface
{
    private CollectionFactory $mappingCollectionFactory;
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private ProductAttributeOptionManagementInterface $attributeOptionManagement;
    private AttributeOptionInterfaceFactory $attributeOptionFactory;

    private array $attributeMapping;
    public array $parsedData = [];

    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param CollectionFactory $mappingCollectionFactory
     * @param ProductAttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionInterfaceFactory $attributeOptionFactory
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        CollectionFactory $mappingCollectionFactory,
        ProductAttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionInterfaceFactory $attributeOptionFactory
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->mappingCollectionFactory = $mappingCollectionFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->attributeMapping = [];
    }

    /**
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Exception
     */
    public function parse(array $data): array
    {
        $attributesMapping = $this->getSortedCustomAttributeMapping();

        return $this->parseData($data, $attributesMapping);
    }

    /**
     * @param array $item
     * @param array $attributesMapping
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
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

            if (!is_null($value)) {
                $attribute = $this->productAttributeRepository->get($magentoCode);

                if ($attribute->getFrontendInput() === 'select') {
                    if ($attributeMapping['katana_attribute_type'] !== 'select') {
                        throw new \Exception('Katana and magento attribute types do not match.');
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
     * @return array
     */
    private function getSortedCustomAttributeMapping()
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
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function getSortedSpecifications(array $data)
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
     * @param ProductAttributeInterface $attribute
     * @param string $value
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
     * @param ProductAttributeInterface $attribute
     * @param string $value
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createNewOption(string $value, ProductAttributeInterface $attribute): void
    {
        $option = $this->attributeOptionFactory->create();
        $option->setValue($value);
        $option->setLabel($value);

        $attributeCode = $attribute->getAttributeCode();
        $this->attributeOptionManagement->add($attributeCode, $option);
    }

    /**
     * @param array $parsedData
     * @return DataParserInterface
     */
    public function setParsedData(array $parsedData): DataParserInterface
    {
        $this->parsedData = $parsedData;

        return $this;
    }
}
