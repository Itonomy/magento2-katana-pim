<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Config\Backend;

use Itonomy\Katanapim\Block\Adminhtml\Form\Field\StoreViewLanguages as StoreViewLanguagesField;
use Magento\Config\Model\Config\Backend\Serialized;

/**
 * Class StoreViewLanguages
 */
class StoreViewLanguages extends Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     * @return StoreViewLanguages
     * @throws \InvalidArgumentException
     */
    public function beforeSave()
    {
        $values = $this->getValue();

        if (is_array($values)) {
            unset($values['__empty']);

            $this->validateStoreViews($values);
        }

        $this->setValue($values);

        return parent::beforeSave();
    }

    /**
     * Validate that maximum one value per store view is set
     *
     * @param array $values
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateStoreViews(array $values): void
    {
        $chosenStoreViews = [];

        foreach ($values as $value) {
            $sw = $value[StoreViewLanguagesField::STORE_VIEW_ID];

            if (array_key_exists($sw, $chosenStoreViews)) {
                throw new \InvalidArgumentException('Please set only one language per store view.');
            }

            $chosenStoreViews[$sw] = $sw;
        }
    }
}
