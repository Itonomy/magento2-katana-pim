<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreViewColumn
 */
class StoreViewColumn extends Select
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    public function __construct(StoreManagerInterface $storeManager, Context $context, array $data = [])
    {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Get source options for the store view column
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $stores = $this->storeManager->getStores(false);
        $options = [];

        foreach ($stores as $store) {
            if ($store->getCode() === 'default') {
                continue;
            }

            $options[] = [
                'label' => $store->getName(),
                'value' => $store->getId()
            ];
        }

        return $options;
    }
}
