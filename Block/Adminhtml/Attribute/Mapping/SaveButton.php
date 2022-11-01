<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Block\Adminhtml\Attribute\Mapping;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save Rows'),
            'class' => 'save primary',
            'on_click' => "setLocation('". $this->getUrl('katanapim/attribute/save') ."'')",
            'sort_order' => 90,
        ];
    }
}
