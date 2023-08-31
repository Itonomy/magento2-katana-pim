<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Process\SpecificationsLocalization;

use Itonomy\Katanapim\Model\Config\Katana;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeOptionUpdateInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for importing specification (attribute) options
 */
class SpecificationOptions
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    private AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory;

    /**
     * @var AttributeOptionManagementInterface
     */
    private AttributeOptionManagementInterface $attributeOptionManagement;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    private AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory;

    /**
     * @var AttributeOptionUpdateInterface
     */
    private AttributeOptionUpdateInterface $attributeOptionUpdate;

    /**
     * @var Katana
     */
    private Katana $katanaConfig;

    /**
     * @var array
     */
    private array $localesToStoreIds;

    /**
     * SpecificationOptions constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory
     * @param AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionUpdateInterface $attributeOptionUpdate
     * @param Katana $katanaConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory,
        AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionUpdateInterface $attributeOptionUpdate,
        Katana $katanaConfig
    ) {
        $this->storeManager = $storeManager;
        $this->attributeOptionInterfaceFactory = $attributeOptionInterfaceFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOptionLabelInterfaceFactory = $attributeOptionLabelInterfaceFactory;
        $this->attributeOptionUpdate = $attributeOptionUpdate;
        $this->katanaConfig = $katanaConfig;
        $this->localesToStoreIds = [];
    }

    /**
     * Process attribute options
     *
     * @param array $optionsData
     * @param ProductAttributeInterface $productAttribute
     * @throws InputException
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function process(array $optionsData, ProductAttributeInterface $productAttribute): void
    {
        $existingOptions = $productAttribute->setStoreId(Store::DEFAULT_STORE_ID)->getOptions();

        foreach ($optionsData as $optionData) {
            $defaultScopeName = \trim($optionData['Name']);

            $option = $this->findExistingOption($existingOptions, $defaultScopeName);

            if ($option) {
                $this->updateExistingOption($productAttribute, $option, $optionData);
            } else {
                $this->createOption($productAttribute, $defaultScopeName, $optionData);
            }
        }
    }

    /**
     * Check if attribute option value already created.
     *
     * @param array $existingOptions
     * @param string $label
     *
     * @return AttributeOptionInterface|null
     */
    private function findExistingOption(array $existingOptions, string $label): ?AttributeOptionInterface
    {
        foreach ($existingOptions as $option) {
            if ($this->mbStrcasecmp($option->getLabel(), $label) === 0) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Update existing attribute option with new labels
     *
     * @param AttributeInterface $attribute
     * @param AttributeOptionInterface $option
     * @param array $optionData
     * @throws InputException
     */
    private function updateExistingOption(
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        array $optionData
    ): void {
        $storeLabels = $this->createStoreLabels($optionData);
        if (!empty($storeLabels)) {
            $option->setStoreLabels($storeLabels);
            try {
                $this->attributeOptionUpdate->update(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $attribute->getAttributeCode(),
                    (int)$option->getValue(),
                    $option
                );
            } catch (\Throwable $exception) {
                throw new InputException(__(
                    'Error while trying to update product attribute %1 with option %2',
                    $attribute->getAttributeCode(),
                    $option->getLabel()
                ));
            }
        }
    }

    /**
     * Create new option.
     *
     * @param AttributeInterface $attribute
     * @param string $label
     * @param array $optionData
     * @return void
     * @throws InputException
     * @throws StateException
     */
    private function createOption(AttributeInterface $attribute, string $label, array $optionData): void
    {
        $option = $this->attributeOptionInterfaceFactory->create();
        $option->setLabel($label);
        $storeLabels = $this->createStoreLabels($optionData);
        $option->setStoreLabels($storeLabels);
        try {
            $this->attributeOptionManagement->add(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attribute->getAttributeCode(),
                $option
            );
        } catch (\Throwable $exception) {
            throw new InputException(__(
                'Error while trying to create product attribute %1 with option %2',
                $attribute->getAttributeCode(),
                $label
            ));
        }
    }

    /**
     * Create store labels
     *
     * @param array $optionData
     * @return array
     */
    private function createStoreLabels(array $optionData): array
    {
        $storeLabels = [];

        $defaultStore = $this->storeManager->getDefaultStoreView();

        if ($defaultStore === null) {
            throw new \LogicException('Default Store View not found while setting attribute option values.');
        }

        $optionLabel = $this->attributeOptionLabelInterfaceFactory->create();
        $defaultName = \trim($optionData['Name']);
        $optionLabel->setStoreId($defaultStore->getId())->setLabel($defaultName);
        $storeLabels[$defaultStore->getId()] = $optionLabel;

        foreach ($optionData['LocalizedProperties'] as $prop) {
            if ($prop['LocaleKey'] !== 'Name') {
                continue;
            }

            $storeIds = $this->findLanguageStoreIds($prop['LanguageCulture']);
            $label = \trim($prop['LocaleValue']);

            if (empty($label)) {
                continue;
            }

            foreach ($storeIds as $storeId) {
                if ($storeId === (int) $defaultStore->getId()) {
                    unset($storeLabels[$storeId]);
                }
                $optionLabel = $this->attributeOptionLabelInterfaceFactory->create();
                $optionLabel->setStoreId($storeId)->setLabel($label);
                $storeLabels[$storeId] = $optionLabel;
            }
        }

        return $storeLabels;
    }

    /**
     * Find store ids which use this katana language code
     *
     * @param string $languageCode
     * @return array
     */
    private function findLanguageStoreIds(string $languageCode): array
    {
        if (empty($this->localesToStoreIds)) {
            foreach ($this->katanaConfig->getLanguageMapping() as $storeId => $langCode) {
                $this->localesToStoreIds[$langCode][] = $storeId;
            }
        }

        return $this->localesToStoreIds[$languageCode] ?? [];
    }

    /**
     * Compare two strings
     *
     * @param string $str1
     * @param string $str2
     * @return int
     */
    private function mbStrcasecmp(string $str1, string $str2): int
    {
        $encoding = \mb_internal_encoding();

        return \strcmp(
            \trim(\mb_strtoupper($str1, $encoding)),
            \trim(\mb_strtoupper($str2, $encoding))
        );
    }
}
