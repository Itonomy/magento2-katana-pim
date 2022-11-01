<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Action;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\Store;

/**
 * Class responsible for validation attribute option values.
 * If value not created - create it.
 */
class ValidateAttributeOptionValue
{
    private AttributeRepositoryInterface $attributeRepository;

    private AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory;

    private AttributeOptionManagementInterface $attributeOptionManagement;

    private AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory
     * @param AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory,
        AttributeOptionLabelInterfaceFactory $attributeOptionLabelInterfaceFactory,
        AttributeOptionManagementInterface $attributeOptionManagement
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionInterfaceFactory = $attributeOptionInterfaceFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOptionLabelInterfaceFactory = $attributeOptionLabelInterfaceFactory;
    }

    /**
     * @param array $attributeValues
     * @return void
     * @throws InputException
     * @throws StateException
     */
    public function execute(array $attributeValues): void
    {
        foreach ($attributeValues as $attributeCode => $values) {
            foreach ($values as $value) {
                $this->validateAttributeValue($attributeCode, $value);
            }
        }
    }

    /**
     * Validate attribute value. Save not existing attribute option value.
     *
     * @param string $attributeCode
     * @param string $value
     *
     * @return void
     * @throws InputException
     * @throws StateException
     */
    private function validateAttributeValue(string $attributeCode, string $value): void
    {
        if ('' === $value) {
            return;
        }

        try {
            $attribute = $this->attributeRepository->get(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        } catch (NoSuchEntityException $exception) {
            return;
        }

        if (!$attribute->usesSource()) {
            return;
        }

        if (true === $this->isAttributeOptionValueCreated($attribute, $value)) {
            return;
        }

        $this->createOption($attribute, $value);
    }

    /**
     * Create new option.
     *
     * @param AttributeInterface $attribute
     * @param string $label
     *
     * @throws InputException
     * @throws StateException
     */
    private function createOption(AttributeInterface $attribute, string $label): void
    {
        $option = $this->attributeOptionInterfaceFactory->create();
        $optionLabel = $this->attributeOptionLabelInterfaceFactory->create();
        $option->setLabel($label)
            ->setSortOrder(0)
            ->setStoreLabels([
                $optionLabel->setStoreId(Store::DEFAULT_STORE_ID)->setLabel($label),
            ]);

        $this->attributeOptionManagement->add(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attribute->getAttributeCode(),
            $option
        );

        $attribute->setOptions(\array_merge($attribute->getOptions(), [$option]));
    }

    /**
     * Check if attribute option value already created.
     *
     * @param AttributeInterface $attribute
     * @param string $label
     *
     * @return bool
     */
    private function isAttributeOptionValueCreated(AttributeInterface $attribute, string $label): bool
    {
        foreach ($attribute->setStoreId(Store::DEFAULT_STORE_ID)->getOptions() as $option) {
            if ($this->mbStrcasecmp($option->getLabel(), $label) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $str1
     * @param string $str2
     * @return int
     */
    private function mbStrcasecmp(string $str1, string $str2): int
    {
        $encoding = \mb_internal_encoding();

        return \strcmp(
            \mb_strtoupper($str1, $encoding),
            \mb_strtoupper($str2, $encoding)
        );
    }
}
