<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Process\SpecificationsLocalization;

use Itonomy\Katanapim\Model\Config\Katana;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\FrontendLabelFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;

class SpecificationTranslation
{
    /**
     * @var Katana
     */
    private Katana $katanaConfig;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private ProductAttributeRepositoryInterface $attributeRepository;

    /**
     * @var FrontendLabelFactory
     */
    private FrontendLabelFactory $frontendLabelFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * SpecificationTranslation constructor.
     *
     * @param Katana $katanaConfig
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param FrontendLabelFactory $frontendLabelFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Katana $katanaConfig,
        ProductAttributeRepositoryInterface $attributeRepository,
        FrontendLabelFactory $frontendLabelFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->katanaConfig = $katanaConfig;
        $this->attributeRepository = $attributeRepository;
        $this->frontendLabelFactory = $frontendLabelFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Process translation of product attribute
     *
     * @param array $localizationData
     * @param ProductAttributeInterface $productAttribute
     * @param string $defaultName
     * @throws CouldNotSaveException
     */
    public function process(array $localizationData, ProductAttributeInterface $productAttribute, string $defaultName): void
    {
        $apiLabels = $this->getApiLabels($localizationData);
        $attributeLabels = $productAttribute->getFrontendLabels();
        $defaultStore = $this->storeManager->getDefaultStoreView();

        foreach ($attributeLabels as $key => $label) {
            if (array_key_exists($label->getStoreId(), $apiLabels)) {
                unset($attributeLabels[$key]);
            }
        }

        $label = $this->frontendLabelFactory->create();
        $defaultName = \trim($defaultName);
        $label->setStoreId($defaultStore->getId())->setLabel($defaultName);
        $attributeLabels[$defaultStore->getId()] = $label;

        foreach ($apiLabels as $storeViewId => $labelValue) {
            if ($storeViewId === (int) $defaultStore->getId()) {
                unset($attributeLabels[$storeViewId]);
            }
            $label = $this->frontendLabelFactory->create();
            $label->setStoreId($storeViewId);
            $label->setLabel($labelValue);
            $attributeLabels[$storeViewId] = $label;
        }

        $productAttribute->setFrontendLabels($attributeLabels);
        try {
            $this->attributeRepository->save($productAttribute);
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(__(
                'Error while trying to saving translation for product attribute with code %1',
                $productAttribute->getAttributeCode()
            ));
        }
    }

    /**
     * Extract attribute labels from api data
     *
     * @param array $localizationData
     * @return array
     */
    private function getApiLabels(array $localizationData): array
    {
        $languageMapping = $this->katanaConfig->getLanguageMapping();
        $apiLabels = [];

        foreach ($languageMapping as $storeViewCode => $language) {
            $apiLabelValue = null;

            foreach ($localizationData as $localeDatum) {
                if ($localeDatum['LocaleKey'] === 'Name' && $localeDatum['LanguageCulture'] === $language) {
                    $apiLabelValue = $localeDatum['LocaleValue'];
                    break;
                }
            }

            if ($apiLabelValue === null) {
                continue;
            }

            $apiLabels[$storeViewCode] = $apiLabelValue;
        }

        return $apiLabels;
    }
}
