<?php
namespace Itonomy\Katanapim\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class StoreViewLanguages extends AbstractFieldArray
{
    public const LANGUAGE_CODE = 'language_code';
    public const STORE_VIEW_ID = 'store_view_id';

    /**
     * @var StoreViewColumn
     */
    private StoreViewColumn $storeRenderer;

    /**
     * Prepare rendering the new field
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(
            self::LANGUAGE_CODE,
            ['label' => __('Katana Language Code'), 'class' => 'required-entry']
        );
        $this->addColumn(self::STORE_VIEW_ID, [
            'label' => __('Store View'),
            'renderer' => $this->getStoreViewRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $sw = $row->getStoreViewId();
        if ($sw !== null) {
            $options['option_' . $this->getStoreViewRenderer()->calcOptionHash($sw)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get store view render
     *
     * @return StoreViewColumn
     * @throws LocalizedException
     */
    private function getStoreViewRenderer(): StoreViewColumn
    {
        if (!($this->storeRenderer ?? null)) {
            $this->storeRenderer = $this->getLayout()->createBlock(
                StoreViewColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->storeRenderer;
    }
}
