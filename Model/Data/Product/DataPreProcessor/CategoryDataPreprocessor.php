<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

class CategoryDataPreprocessor implements PreprocessorInterface
{
    /**
     * Default category name
     */
    private const DEFAULT_CATEGORY = 'Default Category';

    /**
     * @param array $productData
     * @return array
     */
    public function process(array $productData): array
    {
        $productData['categories'] = $this->processCategories($productData);

        return $productData;
    }

    /**
     * @param $productData
     * @return string|null
     */
    private function processCategories($productData)
    {
        $categoriesPaths = [];
        $categoriesPaths[] = self::DEFAULT_CATEGORY;

        foreach ($productData['categories'] as $categoryData) {
            $categoriesPaths[] = $this->getFullCategoryNamePath($categoryData);
        }

        return empty($categoriesPaths) ? null : implode(",", $categoriesPaths);
    }

    /**
     * @param $categoryData
     * @return string
     */
    private function getFullCategoryNamePath($categoryData)
    {
        return self::DEFAULT_CATEGORY . '/' . $this->getCategoryPath($categoryData);
    }

    /**
     * @param $categoryData
     * @return mixed|string
     */
    private function getCategoryPath($categoryData)
    {
        if (is_array($categoryData['ParentCategory'])) {
            return $this->getCategoryPath($categoryData['ParentCategory']) . '/' . $categoryData['Name'];
        } else {
            return $categoryData['Name'];
        }
    }
}
