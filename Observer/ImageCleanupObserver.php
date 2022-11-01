<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Observer;

use Itonomy\Katanapim\Model\Config\Katana;
use Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image\ImageDirectoryProvider;
use Itonomy\Katanapim\Model\Logger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class ImageCleanupObserver
 */
class ImageCleanupObserver implements ObserverInterface
{
    /**
     * @var ImageDirectoryProvider
     */
    private ImageDirectoryProvider $directoryProvider;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var Katana
     */
    private Katana $katanaConfig;

    public function __construct(
        ImageDirectoryProvider $directoryProvider,
        Logger $logger,
        Katana $katanaConfig
    ) {
        $this->directoryProvider = $directoryProvider;
        $this->logger = $logger;
        $this->katanaConfig = $katanaConfig;
    }

    /**
     * Observer for cleaning up katana product image import directory
     *
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->katanaConfig->isCleanImageCacheSet()) {
            try {
                $this->directoryProvider->deleteDirectory();
            } catch (FileSystemException $e) {
                $this->logger->error('Could not clean up KatanaPim image import directory ' . $e->getMessage());
            }
        }
    }
}
