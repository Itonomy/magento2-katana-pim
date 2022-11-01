<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\AttributeMappingRepositoryInterface;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterfaceFactory;
use Itonomy\Katanapim\Model\ResourceModel\AttributeMapping as AttributeMappingResourceModel;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

class AttributeMappingRepository implements AttributeMappingRepositoryInterface
{
    private AttributeMappingResourceModel $attributeMappingResourceModel;

    private AttributeMappingInterfaceFactory $attributeMappingInterfaceFactory;

    /**
     * @param AttributeMappingResourceModel $attributeMappingResourceModel
     * @param AttributeMappingInterfaceFactory $attributeMappingInterfaceFactory
     */
    public function __construct(
        AttributeMappingResourceModel $attributeMappingResourceModel,
        AttributeMappingInterfaceFactory $attributeMappingInterfaceFactory
    ) {
        $this->attributeMappingResourceModel = $attributeMappingResourceModel;
        $this->attributeMappingInterfaceFactory = $attributeMappingInterfaceFactory;
    }

    /**
     * @param AbstractModel $attributeMapping
     * @return AttributeMappingInterface
     * @throws CouldNotSaveException
     */
    public function save(AbstractModel $attributeMapping): AttributeMappingInterface
    {
        try {
            $this->attributeMappingResourceModel->save($attributeMapping);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__(
                'Could not save the Attribute Mapping: %1',
                $e->getMessage()
            ));
        }

        return $attributeMapping;
    }

    /**
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function insertOnDuplicate(array $data, array $fields = []): int
    {
        try {
            return $this->attributeMappingResourceModel->insertOnDuplicate($data, $fields);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__(
                'Could not save the Attribute Mapping: %1',
                $e->getMessage()
            ));
        }
    }

    /**
     * @param int $id
     * @return AttributeMappingInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): AttributeMappingInterface
    {
        $attributeMapping = $this->attributeMappingInterfaceFactory->create();
        $this->attributeMappingResourceModel->load($attributeMapping, $id);

        if (!$attributeMapping->getId()) {
            throw new NoSuchEntityException(__('Requested Attribute Mapping doesn\'t exist. Id: %s', $id));
        }

        return $attributeMapping;
    }

    /**
     * @param array $savedIds
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteMapping(array $savedIds = []): bool
    {
        $this->attributeMappingResourceModel->deleteMapping($savedIds);

        return true;
    }
}
