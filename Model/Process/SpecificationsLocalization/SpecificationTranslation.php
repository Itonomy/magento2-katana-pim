<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Process\SpecificationsLocalization;

use Itonomy\Katanapim\Model\Config\Katana;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\FrontendLabelFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class SpecificationTranslation
{
    private Katana $katanaConfig;
    private ProductAttributeRepositoryInterface $attributeRepository;
    private FrontendLabelFactory $frontendLabelFactory;

    /**
     * SpecificationTranslation constructor.
     *
     * @param Katana $katanaConfig
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param FrontendLabelFactory $frontendLabelFactory
     */
    public function __construct(
        Katana $katanaConfig,
        ProductAttributeRepositoryInterface $attributeRepository,
        FrontendLabelFactory $frontendLabelFactory
    ) {
        $this->katanaConfig = $katanaConfig;
        $this->attributeRepository = $attributeRepository;
        $this->frontendLabelFactory = $frontendLabelFactory;
    }

    /**
     * Process translation of product attribute
     *
     * @param array $localizationData
     * @param ProductAttributeInterface $productAttribute
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InputException
     */
    public function process(array $localizationData, ProductAttributeInterface $productAttribute): void
    {
        $apiLabels = $this->getApiLabels($localizationData);
        $attributeLabels = $productAttribute->getFrontendLabels();

        foreach ($attributeLabels as $key => $label) {
            if (array_key_exists($label->getStoreId(), $apiLabels)) {
                unset($attributeLabels[$key]);
            }
        }

        foreach ($apiLabels as $storeViewId => $labelValue) {
            $label = $this->frontendLabelFactory->create();
            $label->setStoreId($storeViewId);
            $label->setLabel($labelValue);
            $attributeLabels[] = $label;
        }

        $productAttribute->setFrontendLabels($attributeLabels);
        $this->attributeRepository->save($productAttribute);
    }

    /**
     * Extract attribute labels from api data
     *
     * @param $localizationData
     * @return array
     */
    private function getApiLabels($localizationData): array
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

            if (is_null($apiLabelValue)) {
                continue;
            }

            $apiLabels[$storeViewCode] = $apiLabelValue;
        }

        return $apiLabels;
    }
}
