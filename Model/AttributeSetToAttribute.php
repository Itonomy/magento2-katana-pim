<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\AttributeSetToAttributeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class AttributeSetToAttribute extends AbstractModel implements AttributeSetToAttributeInterface, IdentityInterface
{
    public const CACHE_TAG = 'itonomy_katanapim_attribute_set_to_attribute';

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
        $this->_init(ResourceModel\AttributeSetToAttribute::class);
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
    public function setSetId(string $setId): AttributeSetToAttributeInterface
    {
        return $this->setData(self::SET_ID, $setId);
    }

    /**
     * @inheritDoc
     */
    public function getSetId(): ?int
    {
        return $this->getData(self::SET_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeId(string $attributeId): AttributeSetToAttributeInterface
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeId(): ?int
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }
}
