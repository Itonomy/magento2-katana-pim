<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Api\KatanaImportRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class KatanaImportRepository implements KatanaImportRepositoryInterface
{
    /**
     * @var ResourceModel\KatanaImport
     */
    private ResourceModel\KatanaImport $resource;

    /**
     * @var KatanaImportFactory
     */
    private KatanaImportFactory $katanaImportFactory;

    /**
     * @param ResourceModel\KatanaImport $resource
     * @param KatanaImportFactory $katanaImportFactory
     */
    public function __construct(
        \Itonomy\Katanapim\Model\ResourceModel\KatanaImport $resource,
        KatanaImportFactory $katanaImportFactory
    ) {
        $this->resource = $resource;
        $this->katanaImportFactory = $katanaImportFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws CouldNotSaveException
     */
    public function save(KatanaImportInterface $katanaImport): KatanaImportInterface
    {
        try {
            $this->resource->save($katanaImport);
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(
                __('Could not save the page: %1', __('Something went wrong while saving the page.')),
                $exception
            );
        }

        return $katanaImport;
    }

    /**
     * @inheritDoc
     *
     * @throws NoSuchEntityException
     */
    public function getById($id): KatanaImportInterface
    {
        $entityLog = $this->katanaImportFactory->create();
        $this->resource->load($entityLog, $id);
        if (!$entityLog->getId()) {
            throw new NoSuchEntityException(__('Katana Import with the "%1" ID doesn\'t exist.', $id));
        }
        return $entityLog;
    }
}
