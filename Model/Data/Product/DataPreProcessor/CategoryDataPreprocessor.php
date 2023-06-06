<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

use Itonomy\Katanapim\Model\Config\Katana;

class CategoryDataPreprocessor implements PreprocessorInterface
{
    /**
     * Default category name
     */
    private const DEFAULT_CATEGORY = 'Default Category';

    /**
     * @var Katana
     */
    private Katana $katanaConfig;

    /**
     * @param Katana $katanaConfig
     */
    public function __construct(Katana $katanaConfig)
    {
        $this->katanaConfig = $katanaConfig;
    }

    /**
     * @inheritDoc
     *
     * @param array $productData
     * @return array
     */
    public function process(array $productData): array
    {
        if ($this->katanaConfig->isCategoryImportEnabled()) {
            $productData['categories'] = $this->processCategories($productData);
        } else {
            $productData['categories'] = '';
        }

        return $productData;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return [];
    }

    /**
     * Process categories data
     *
     * @param array $productData
     * @return string|null
     */
    private function processCategories(array $productData): ?string
    {
        $categoriesPaths = [];
        $categoriesPaths[] = self::DEFAULT_CATEGORY;

        foreach ($productData['categories'] as $categoryData) {
            $categoriesPaths[] = $this->getFullCategoryNamePath($categoryData);
        }

        return empty($categoriesPaths) ? null : implode(",", $categoriesPaths);
    }

    /**
     * Get full category names path
     *
     * @param array $categoryData
     * @return string
     */
    private function getFullCategoryNamePath(array $categoryData): string
    {
        return self::DEFAULT_CATEGORY . '/' . $this->getCategoryPath($categoryData);
    }

    /**
     * Get category path
     *
     * @param array $categoryData
     * @return string
     */
    private function getCategoryPath(array $categoryData): string
    {
        if (is_array($categoryData['ParentCategory'])) {
            return $this->getCategoryPath($categoryData['ParentCategory']) . '/' . $categoryData['Name'];
        } else {
            return $categoryData['Name'];
        }
    }
}
