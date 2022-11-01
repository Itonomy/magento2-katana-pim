<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\AttributeSetInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class AttributeSet extends AbstractModel implements AttributeSetInterface, IdentityInterface
{
    const CACHE_TAG = 'itonomy_katanapim_attribute_set';

    protected $_cacheTag = self::CACHE_TAG;
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\AttributeSet::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setName(string $name): AttributeSetInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    public function setCode(string $code): AttributeSetInterface
    {
        return $this->setData(self::CODE, $code);
    }

    public function getCode(): ?string
    {
        return $this->getData(self::CODE);
    }
}
