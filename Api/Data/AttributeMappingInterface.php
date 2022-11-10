<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface AttributeMappingInterface
{
    public const ID = 'id';
    public const KATANA_ID = 'katana_id';
    public const KATANA_ATTRIBUTE_CODE = 'katana_attribute_code';
    public const KATANA_ATTRIBUTE_NAME = 'katana_attribute_name';
    public const KATANA_ATTRIBUTE_TYPE = 'katana_attribute_type';
    public const KATANA_ATTRIBUTE_TYPE_ID = 'katana_attribute_type_id';
    public const MAGENTO_ATTRIBUTE_ID = 'magento_attribute_id';
    public const MAGENTO_ATTRIBUTE_CODE = 'magento_attribute_code';
    public const IS_CONFIGURABLE = 'is_configurable';
    public const STORE_ID = 'store';

    /**
     * Get Id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get Katana id
     *
     * @return int|null
     */
    public function getKatanaId(): ?int;

    /**
     * Set Katana Id
     *
     * @param int $katanaId
     * @return $this
     */
    public function setKatanaId(int $katanaId): self;

    /**
     * Get Katana Attribute Code
     *
     * @return string|null
     */
    public function getKatanaAttributeCode(): ?string;

    /**
     * Set Katana Attribute Code
     *
     * @param string $katanaAttributeCode
     * @return $this
     */
    public function setKatanaAttributeCode(string $katanaAttributeCode): self;

    /**
     * Get Katana Attribute Name
     *
     * @return string|null
     */
    public function getKatanaAttributeName(): ?string;

    /**
     * Set Katana Attribute Name
     *
     * @param string $katanaAttributeName
     * @return $this
     */
    public function setKatanaAttributeName(string $katanaAttributeName): self;

    /**
     * Get Katana Attribute Type
     *
     * @return string|null
     */
    public function getKatanaAttributeType(): ?string;

    /**
     * Set Katana Attribute Type
     *
     * @param string $katanaAttributeType
     * @return $this
     */
    public function setKatanaAttributeType(string $katanaAttributeType): self;

    /**
     * Get Katana Attribute Type Id
     *
     * @return string|null
     */
    public function getKatanaAttributeTypeId(): ?string;

    /**
     * Set Katana Attribute Type Id
     *
     * @param string $katanaAttributeTypeId
     * @return $this
     */
    public function setKatanaAttributeTypeId(string $katanaAttributeTypeId): self;

    /**
     * Get Magento Attribute Id
     *
     * @return string
     */
    public function getMagentoAttributeId(): string;

    /**
     * Set Magento Attribute Id
     *
     * @param string $magentoAttributeId
     * @return $this
     */
    public function setMagentoAttributeId(string $magentoAttributeId): self;

    /**
     * Get Magento Attribute Code
     *
     * @return string
     */
    public function getMagentoAttributCode(): string;

    /**
     * Set Magento Attribute Code
     *
     * @param string $magentoAttributeCode
     * @return $this
     */
    public function setMagentoAttributeCode(string $magentoAttributeCode): self;

    /**
     * Get Is Configurable
     *
     * @return bool
     */
    public function getIsConfigurable(): bool;

    /**
     * Set Is Configurable
     *
     * @param bool $isConfigurable
     * @return $this
     */
    public function setIsConfigurable(bool $isConfigurable): self;

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set store id
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId(int $store): self;

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;
}
