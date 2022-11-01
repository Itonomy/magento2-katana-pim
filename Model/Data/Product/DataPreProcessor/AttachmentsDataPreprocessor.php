<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

use Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image\FileDownloader;
use Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image\ImageDirectoryProvider;
use Itonomy\Katanapim\Model\Logger;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class ImageDataPreprocessor
 */
class AttachmentsDataPreprocessor implements PreprocessorInterface
{
    /**
     * Default category name
     */
    private const KATANA_ATTACHMENTS = 'katana_attachments';

    /**
     * @param array $productData
     * @return mixed
     */
    public function process(array $productData): array
    {
        $productData[self::KATANA_ATTACHMENTS] = $this->processAttachments($productData);

        return $productData;
    }

    /**
     * @param array $productData
     * @return string|null
     */
    private function processAttachments(array $productData): ?string
    {
        $attachments = [];

        foreach ($productData[self::KATANA_ATTACHMENTS] as $attachment) {
            $attachments[] = $attachment['Url'];
        }

        return empty($attachments) ? null : implode(",", $attachments);
    }
}
