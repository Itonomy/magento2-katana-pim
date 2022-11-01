<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface AttributeSetToAttributeInterface
{
    public const ID = 'id';
    public const SET_ID = 'set_id';
    public const ATTRIBUTE_ID = 'attribute_id';

    public function setSetId(string $setId): self;

    public function getSetId(): ?int;

    public function setAttributeId(string $attributeId): self;

    public function getAttributeId(): ?int;
}
