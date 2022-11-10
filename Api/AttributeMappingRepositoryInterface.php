<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Api;

use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Itonomy\Katanapim\Model\AttributeMapping;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

interface AttributeMappingRepositoryInterface
{

    /**
     * Save attribute mapping row
     *
     * @param AttributeMapping $attributeMapping
     *
     * @return AttributeMappingInterface
     * @throws CouldNotSaveException
     */
    public function save(AttributeMapping $attributeMapping): AttributeMappingInterface;

    /**
     * Get attribute mapping row
     *
     * @param int $id
     *
     * @return AttributeMappingInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): AttributeMappingInterface;
}
