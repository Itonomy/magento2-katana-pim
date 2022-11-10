<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Attribute mapping model
 */
class AttributeMapping extends AbstractModel implements AttributeMappingInterface, IdentityInterface
{
    public const CACHE_TAG = 'itonomy_katanapim_attribute_mapping';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\AttributeMapping::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getKatanaId(): ?int
    {
        return $this->getData(self::KATANA_ID);
    }

    /**
     * @inheritDoc
     */
    public function setKatanaId(int $katanaId): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ID, $katanaId);
    }

    /**
     * @inheritDoc
     */
    public function getKatanaAttributeCode(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setKatanaAttributeCode(string $katanaAttributeCode): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_CODE, $katanaAttributeCode);
    }

    /**
     * @inheritDoc
     */
    public function getKatanaAttributeName(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setKatanaAttributeName(string $katanaAttributeName): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_NAME, $katanaAttributeName);
    }

    /**
     * @inheritDoc
     */
    public function getKatanaAttributeType(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setKatanaAttributeType(string $katanaAttributeType): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_TYPE, $katanaAttributeType);
    }

    /**
     * @inheritDoc
     */
    public function getKatanaAttributeTypeId(): ?string
    {
        return $this->getData(self::KATANA_ATTRIBUTE_TYPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setKatanaAttributeTypeId(string $katanaAttributeTypeId): AttributeMappingInterface
    {
        return $this->setData(self::KATANA_ATTRIBUTE_TYPE_ID, $katanaAttributeTypeId);
    }

    /**
     * @inheritDoc
     */
    public function getMagentoAttributeId(): string
    {
        return $this->getData(self::MAGENTO_ATTRIBUTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMagentoAttributeId(string $magentoAttributeId): AttributeMappingInterface
    {
        return $this->setData(self::MAGENTO_ATTRIBUTE_ID, $magentoAttributeId);
    }

    /**
     * @inheritDoc
     */
    public function getMagentoAttributCode(): string
    {
        return $this->getData(self::MAGENTO_ATTRIBUTE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setMagentoAttributeCode(string $magentoAttributeCode): AttributeMappingInterface
    {
        return $this->setData(self::MAGENTO_ATTRIBUTE_CODE, $magentoAttributeCode);
    }

    /**
     * @inheritDoc
     */
    public function getIsConfigurable(): bool
    {
        return (bool)$this->getData(self::IS_CONFIGURABLE);
    }

    /**
     * @inheritDoc
     */
    public function setIsConfigurable(bool $isConfigurable): AttributeMappingInterface
    {
        return $this->setData(self::IS_CONFIGURABLE, (int)$isConfigurable);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): int
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId(int $store): AttributeMappingInterface
    {
        return $this->setData(self::STORE_ID, $store);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getKatanaAttributeName() . ' [' . $this->getKatanaAttributeType() . ']';
    }
}
