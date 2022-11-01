<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class AttributeMapping extends AbstractModel implements AttributeMappingInterface, IdentityInterface
{
    const CACHE_TAG = 'itonomy_katanapim_attribute_mapping';

    protected $_cacheTag = self::CACHE_TAG;
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\AttributeMapping::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getKatanaId(): ?int
    {
        return $this->getData(self::KATANA_ID);
    }

    public function setKatanaId(int $katanaId): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ID, $katanaId);
    }

    public function getKatanaAttributeCode(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_CODE);
    }

    public function setKatanaAttributeCode(string $katanaAttributeCode): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_CODE, $katanaAttributeCode);
    }

    public function getKatanaAttributeName(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_NAME);
    }

    public function setKatanaAttributeName(string $katanaAttributeName): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_NAME, $katanaAttributeName);
    }

    public function getKatanaAttributeType(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_TYPE);
    }

    public function setKatanaAttributeType(string $katanaAttributeType): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_TYPE, $katanaAttributeType);
    }

    public function getKatanaAttributeTypeId(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_TYPE_ID);
    }

    public function setKatanaAttributeTypeId(string $katanaAttributeTypeId): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_TYPE_ID, $katanaAttributeTypeId);
    }

    public function getMagentoAttributeId(): string
    {
        return $this->getData(self::MAGENTO_ATTRIBUTE_ID);
    }

    public function setMagentoAttributeId(string $magentoAttributeId): AttributeMappingInterface
    {
        return $this->setData(self::MAGENTO_ATTRIBUTE_ID, $magentoAttributeId);
    }

    public function getMagentoAttributCode(): string
    {
        return $this->getData(self::MAGENTO_ATTRIBUTE_CODE);
    }

    public function setMagentoAttributeCode(string $magentoAttributeCode): AttributeMappingInterface
    {
        return $this->setData(self::MAGENTO_ATTRIBUTE_CODE, $magentoAttributeCode);
    }

    public function getIsConfigurable(): bool
    {
        return (bool)$this->getData(self::IS_CONFIGURABLE);
    }

    public function setIsConfigurable(bool $isConfigurable): AttributeMappingInterface
    {
        return $this->setData(self::IS_CONFIGURABLE, (int)$isConfigurable);
    }

    public function getStoreId(): int
    {
        return $this->getData(self::STORE_ID);
    }

    public function setStoreId(int $store): AttributeMappingInterface
    {
        return $this->setData(self::STORE_ID, $store);
    }

    public function getName(): string
    {
        return $this->getKatanaAttributeName() . ' [' . $this->getKatanaAttributeType() . ']';
    }
}
