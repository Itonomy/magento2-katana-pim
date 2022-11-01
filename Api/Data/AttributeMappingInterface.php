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

    public function getId();

    public function getKatanaId(): ?int;

    public function setKatanaId(int $katanaId): self;

    public function getKatanaAttributeCode(): ?string;

    public function setKatanaAttributeCode(string $katanaAttributeCode): self;

    public function getKatanaAttributeName(): ?string;

    public function setKatanaAttributeName(string $katanaAttributeName): self;

    public function getKatanaAttributeType(): ?string;

    public function setKatanaAttributeType(string $katanaAttributeType): self;

    public function getKatanaAttributeTypeId(): ?string;

    public function setKatanaAttributeTypeId(string $katanaAttributeTypeId): self;

    public function getMagentoAttributeId(): string;

    public function setMagentoAttributeId(string $magentoAttributeId): self;

    public function getMagentoAttributCode(): string;

    public function setMagentoAttributeCode(string $magentoAttributeCode): self;

    public function getIsConfigurable(): bool;

    public function setIsConfigurable(bool $isConfigurable): self;

    public function getStoreId(): int;

    public function setStoreId(int $store): self;

    public function getName(): string;
}
