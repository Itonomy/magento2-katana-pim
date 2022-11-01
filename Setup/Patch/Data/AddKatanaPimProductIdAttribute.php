<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Api\Data\AttributeSetInterfaceFactory;
use Zend_Validate_Exception;

class AddKatanaPimProductIdAttribute implements DataPatchInterface
{
    public const KATANA_PRODUCT_ID_ATTRIBUTE_CODE = 'katana_pim_id';
    public const KATANA_PRODUCT_ID_ATTRIBUTE_LABEL = 'KatanaPim ID';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply patch
     *
     * @return $this|DataPatchInterface
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if ($eavSetup->getAttribute(Product::ENTITY, self::KATANA_PRODUCT_ID_ATTRIBUTE_CODE)) {
            $eavSetup->removeAttribute(
                Product::ENTITY,
                self::KATANA_PRODUCT_ID_ATTRIBUTE_CODE
            );
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::KATANA_PRODUCT_ID_ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'input' => 'text',
                'label' => self::KATANA_PRODUCT_ID_ATTRIBUTE_LABEL,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible_on_front' => 0,
                'required' => 0,
                'is_used_in_grid' => 1,
                'is_visible_in_grid' => 0,
                'is_filterable_in_grid' => 1,
                'group' => 'Katana Pim',
            ]
        );

        $eavSetup->addAttributeToGroup(
            Product::ENTITY,
            'Default',
            'Katana Pim',
            self::KATANA_PRODUCT_ID_ATTRIBUTE_CODE
        );

        return $this;
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
