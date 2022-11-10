<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface AttributeSetToAttributeInterface
{
    public const ID = 'id';
    public const SET_ID = 'set_id';
    public const ATTRIBUTE_ID = 'attribute_id';

    /**
     * Set Attribute Set Id
     *
     * @param string $setId
     * @return $this
     */
    public function setSetId(string $setId): self;

    /**
     * Get Attribute Set Id
     *
     * @return int|null
     */
    public function getSetId(): ?int;

    /**
     * Set Attribute Id
     *
     * @param string $attributeId
     * @return $this
     */
    public function setAttributeId(string $attributeId): self;

    /**
     * Get Attribute Id
     *
     * @return int|null
     */
    public function getAttributeId(): ?int;
}
