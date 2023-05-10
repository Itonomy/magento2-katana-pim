<?php

declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Api\Data\KatanaImportInterface;
use Itonomy\Katanapim\Api\KatanaImportRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class KatanaImportRepository implements KatanaImportRepositoryInterface
{
    /**
     * @var ResourceModel\KatanaImport
     */
    private ResourceModel\KatanaImport $resource;

    /**
     * @param ResourceModel\KatanaImport $resource
     */
    public function __construct(\Itonomy\Katanapim\Model\ResourceModel\KatanaImport $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param KatanaImportInterface $katanaImport
     * @return KatanaImportInterface
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
}
