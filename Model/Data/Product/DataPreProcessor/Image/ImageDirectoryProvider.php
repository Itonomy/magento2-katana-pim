<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Provides base directory to use for images when user imports entities.
 */
class ImageDirectoryProvider
{
    public const BASE_DIR_IMAGES_FOLDER = 'import';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * ImageDirectoryProvider constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Base directory that users are allowed to place images for importing.
     *
     * @return ReadInterface
     */
    public function getBaseDirectoryRead(): ReadInterface
    {
        $read = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $path = $read->getAbsolutePath(self::BASE_DIR_IMAGES_FOLDER);

        return $this->filesystem->getDirectoryReadByPath(
            $path
        );
    }

    /**
     * Directory that users are allowed to place images for importing.
     *
     * @return void
     * @throws FileSystemException
     */
    public function deleteDirectory(): void
    {
        $read = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $write = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = $read->getAbsolutePath(self::BASE_DIR_IMAGES_FOLDER);

        if ($read->isExist($path)) {
            $write->delete($path);
        }
    }

    /**
     * Get absolute path
     *
     * @return string
     * @throws FileSystemException
     */
    public function getDirectoryAbsolutePath(): string
    {
        $write = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = $write->getAbsolutePath(self::BASE_DIR_IMAGES_FOLDER);

        if (!$write->isWritable($path)) {
            $write->create($path);
        }

        return $path;
    }
}
