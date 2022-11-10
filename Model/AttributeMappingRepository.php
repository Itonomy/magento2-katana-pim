<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\AttributeMappingRepositoryInterface;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterface;
use Itonomy\Katanapim\Api\Data\AttributeMappingInterfaceFactory;
use Itonomy\Katanapim\Model\ResourceModel\AttributeMapping as AttributeMappingResourceModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeMappingRepository implements AttributeMappingRepositoryInterface
{
    /**
     * @var AttributeMappingResourceModel
     */
    private AttributeMappingResourceModel $attributeMappingResourceModel;

    /**
     * @var AttributeMappingInterfaceFactory
     */
    private AttributeMappingInterfaceFactory $attributeMappingInterfaceFactory;

    /**
     * AttributeMappingRepository constructor.
     *
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
     * @inheritDoc
     *
     * @param AttributeMapping $attributeMapping
     * @return AttributeMappingInterface
     * @throws CouldNotSaveException
     */
    public function save(AttributeMapping $attributeMapping): AttributeMappingInterface
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
     * Insert a table row with specified data
     *
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     * @throws CouldNotSaveException
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
     * Get attribute mapping by id
     *
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
     * Delete attribute mapping
     *
     * @param array $savedIds
     * @return bool
     * @throws LocalizedException
     */
    public function deleteMapping(array $savedIds = []): bool
    {
        $this->attributeMappingResourceModel->deleteMapping($savedIds);

        return true;
    }
}
