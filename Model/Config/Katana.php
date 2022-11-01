<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Config;

use Itonomy\Katanapim\Block\Adminhtml\Form\Field\StoreViewLanguages;
use Magento\Framework\App\Helper\AbstractHelper;

class Katana extends AbstractHelper
{
    private const API_URL = 'katanapim_general/api/url';

    private const API_KEY = 'katanapim_general/api/key';

    private const PRODUCT_IMPORT_ENABLED = 'katanapim_product_import/general/enabled';

    private const CLEAN_IMAGES_AFTER_IMPORT = 'katanapim_product_import/general/delete_temporary_images';

    private const LANGUAGE_MAPPING = 'katanapim_product_import/store_languages/store_language_mapping';

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::API_URL);
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::API_KEY);
    }

    /**
     * @return bool
     */
    public function isProductImportEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PRODUCT_IMPORT_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCleanImageCacheSet(): bool
    {
        return $this->scopeConfig->isSetFlag(self::CLEAN_IMAGES_AFTER_IMPORT);
    }

    /**
     * @return array
     */
    public function getLanguageMapping(): array
    {
        $json = $this->scopeConfig->getValue(self::LANGUAGE_MAPPING);
        $output = [];

        if (is_string($json)) {
            $data = \json_decode($json, true);

            foreach ($data as $row) {
                $output[$row[StoreViewLanguages::STORE_VIEW_ID]] = $row[StoreViewLanguages::LANGUAGE_CODE];
            }
        }

        return $output;
    }
}
