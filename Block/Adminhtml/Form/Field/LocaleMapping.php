<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Dynamic rows locale mapping.
 */
class LocaleMapping extends AbstractFieldArray
{
    /**
     * @var ElementFactory
     */
    private ElementFactory $elementFactory;

    /**
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->elementFactory = $elementFactory;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'magento_locale',
            [
                'label' => __('Magento Locale'),
                'class' => 'required-entry',
            ]
        );
        $this->addColumn(
            'external_locale',
            [
                'label' => __('External Locale'),
                'class' => 'required-entry',
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @inheritdoc
     */
    public function renderCellTemplate($columnName): string
    {
        if ($columnName === 'magento_locale' && isset($this->_columns[$columnName])) {
            return \str_replace(PHP_EOL, '', $this->renderMagentoLocaleColumn($columnName));
        }

        return parent::renderCellTemplate($columnName);
    }

    /**
     * Render magento locale column.
     *
     * @param string $columnName
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function renderMagentoLocaleColumn(string $columnName): string
    {
        $options = \array_map(function (StoreInterface $store) {
            return [
                'value' => $store->getId(),
                'label' => \sprintf(
                    '%s / %s / %s',
                    $store->getWebsite()->getName(),
                    $store->getGroupId() !== null ? $store->getGroup()->getName() : '',
                    $store->getName()
                ),
            ];
        }, $this->_storeManager->getStores());

        return $this->renderSelect($columnName, $options);
    }

    /**
     * Renders select element.
     *
     * @param string $columnName
     * @param array $options
     *
     * @return string
     */
    private function renderSelect(string $columnName, array $options): string
    {
        return $this->elementFactory
            ->create('select')
            ->setForm($this->getData('form'))
            ->setData('name', $this->_getCellInputElementName($columnName))
            ->setData('html_id', $this->_getCellInputElementId('<%- _id %>', $columnName))
            ->setData('values', $options)
            ->getElementHtml();
    }
}
