<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Api\Data;

interface AttributeSetInterface
{
    public const ID = 'id';
    public const NAME = 'name';
    public const CODE = 'code';

    /**
     * Get Id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return mixed
     */
    public function setId(int $id);

    /**
     * Set Name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): self;

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode(): ?string;
}
