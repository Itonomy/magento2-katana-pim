<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\AttributeSetToAttributeRepositoryInterface;
use Itonomy\Katanapim\Model\ResourceModel\AttributeSetToAttribute as ASTAResourceModel;

use Magento\Framework\Exception\CouldNotSaveException;

class AttributeSetToAttributeRepository implements AttributeSetToAttributeRepositoryInterface
{
    /**
     * @var array
     */
    private array $codes = [];

    /**
     * @var ASTAResourceModel
     */
    private ASTAResourceModel $astaResourceModel;

    /**
     * @param ASTAResourceModel $astaResourceModel
     */
    public function __construct(
        ASTAResourceModel $astaResourceModel
    ) {
        $this->astaResourceModel = $astaResourceModel;
    }

    /**
     * Inserts a table row with specified data
     *
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function insertOnDuplicate(array $data, array $fields = []): int
    {
        try {
            return $this->astaResourceModel->insertOnDuplicate($data, $fields);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__(
                'Could not save the Attribute Set: %1',
                $e->getMessage()
            ));
        }
    }

    /**
     * Get configurable product variation codes
     *
     * @param int $specificationGroupId
     * @return array ['katana_color' => 'color', 'katana_size' => 'size'...]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigurableVariationsCodes(int $specificationGroupId): array
    {
        if (!isset($this->codes[$specificationGroupId])) {
            //phpcs:ignore Generic.Files.LineLength.TooLong
            $this->codes[$specificationGroupId] = $this->astaResourceModel->getConfigurableVariationsCodes($specificationGroupId);
        }

        return $this->codes[$specificationGroupId];
    }
}
