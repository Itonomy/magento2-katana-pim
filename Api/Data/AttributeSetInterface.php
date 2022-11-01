<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface AttributeSetInterface
{
    public const ID = 'id';
    public const NAME = 'name';
    public const CODE = 'code';

    public function getId();

    public function setId(int $id);

    public function setName(string $name): self;

    public function getName(): ?string;

    public function setCode(string $code): self;

    public function getCode(): ?string;
}
