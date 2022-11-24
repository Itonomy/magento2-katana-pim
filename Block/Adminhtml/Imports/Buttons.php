<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Block\Adminhtml\Imports;

use Itonomy\Katanapim\Controller\Adminhtml\Imports\ImportProducts;
use Itonomy\Katanapim\Controller\Adminhtml\Imports\ImportSpecifications;
use Itonomy\Katanapim\Controller\Adminhtml\Imports\ImportSpecificationTranslations;
use Magento\Backend\Block\Template;

class Buttons extends Template
{
    /**
     * Get import products url
     *
     * @return string
     */
    public function getImportProductsUrl(): string
    {
        return $this->getUrl('*/*/importProducts');
    }

    /**
     * Get import specifications url
     *
     * @return string
     */
    public function getImportSpecificationsUrl(): string
    {
        return $this->getUrl('*/*/importSpecifications');
    }

    /**
     * Get import specification translations and options url
     *
     * @return string
     */
    public function getImportSpecificationTranslationsUrl(): string
    {
        return $this->getUrl('*/*/importSpecificationTranslations');
    }

    /**
     * Get has access to products import resource
     *
     * @return bool
     */
    public function hasAccessToProductsImport(): bool
    {
        return $this->_authorization->isAllowed(ImportProducts::ADMIN_RESOURCE);
    }

    /**
     * Get has access to product specifications import resource
     *
     * @return bool
     */
    public function hasAccessToSpecificationsImport(): bool
    {
        return $this->_authorization->isAllowed(ImportSpecifications::ADMIN_RESOURCE);
    }

    /**
     * Get has access to products specification translations and options import resource
     *
     * @return bool
     */
    public function hasAccessToSpecificationTranslationsImport(): bool
    {
        return $this->_authorization->isAllowed(ImportSpecificationTranslations::ADMIN_RESOURCE);
    }
}
