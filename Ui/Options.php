<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Ui;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private AttributeRepositoryInterface $attributeRepository;

    private SortOrderBuilder $sortOrderBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField('frontend_label')
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_visible', 1)
            ->addSortOrder($sortOrder)
            ->create();

        $attributeRepository = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );


        $result = [];
        $result[] = ['value' => '', 'label' => '-'];

        foreach ($attributeRepository->getItems() as $attribute) {
            $result[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel() . '(' . $attribute->getAttributeCode()
                    . ' [' . $attribute->getFrontendInput() . '])'
            ];
        }

        return $result;
    }
}
