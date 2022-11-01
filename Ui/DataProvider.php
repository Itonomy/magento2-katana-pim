<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Ui;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Itonomy\Katanapim\Model\ResourceModel\AttributeMapping\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    private array $loadedData = [];

    private CollectionFactory $collectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $attributeMappingCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $attributeMappingCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $attributeMappingCollectionFactory->create();
        $this->collectionFactory = $attributeMappingCollectionFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collectionFactory->create()
            ->getItems();

        //could be used for store dependency in future
        $store = 0;

        foreach ($items as $item) {
            $item->setData('name', $item->getName());
            $this->loadedData[$store]['itonomy_katanapim_dynamic_rows_container'][] = $item->getData();
            $this->loadedData['items'][] = $item->getData();
        }

        return $this->loadedData;
    }
}
