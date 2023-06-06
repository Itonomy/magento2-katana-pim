<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Observer;

use Itonomy\Katanapim\Model\Config\Katana;
use Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image\ImageDirectoryProvider;
use Itonomy\DatabaseLogger\Model\Logger;
use Itonomy\Katanapim\Model\KatanaImportHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class responsible for cleaning up katana product image import directory
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

    /**
     * ImageCleanupObserver constructor.
     *
     * @param ImageDirectoryProvider $directoryProvider
     * @param Logger $logger
     * @param Katana $katanaConfig
     */
    public function __construct(
        ImageDirectoryProvider $directoryProvider,
        Logger $logger,
        Katana $katanaConfig,
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
    public function execute(Observer $observer): void
    {
        if ($this->katanaConfig->isCleanImageCacheSet()) {
            try {
                $this->directoryProvider->deleteDirectory();
            } catch (FileSystemException $e) {
                $importInfo = $observer->getData('importInfo');

                $this->logger->error(
                    'Could not clean up KatanaPim image import directory ' . $e->getMessage(),
                    [
                        'entity_id' => $importInfo->getImportId(),
                        'entity_type' => $importInfo->getImportType()
                    ]
                );
            }
        }
    }
}
