<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Api;

use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

interface AttributeMappingRepositoryInterface
{

    /**
     * @param AbstractModel $attributeMapping
     *
     * @return AttributeMappingInterface
     * @throws CouldNotSaveException
     */
    public function save(AbstractModel $attributeMapping): AttributeMappingInterface;

    /**
     * @param int $id
     *
     * @return AttributeMappingInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): AttributeMappingInterface;
}
