<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\AttributeSetRepositoryInterface;
use Itonomy\Katanapim\Model\ResourceModel\AttributeSet as AttributeSetResourceModel;

use Magento\Framework\Exception\CouldNotSaveException;

class AttributeSetRepository implements AttributeSetRepositoryInterface
{
    private AttributeSetResourceModel $attributeSetResourceModel;

    /**
     * @param AttributeSetResourceModel $attributeSetResourceModel
     */
    public function __construct(
        AttributeSetResourceModel $attributeSetResourceModel
    ) {
        $this->attributeSetResourceModel = $attributeSetResourceModel;
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
            return $this->attributeSetResourceModel->insertOnDuplicate($data, $fields);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__(
                'Could not save the Attribute Set: %1',
                $e->getMessage()
            ));
        }
    }
}
