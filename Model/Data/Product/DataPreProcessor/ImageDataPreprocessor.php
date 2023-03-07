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

class ImageDataPreprocessor implements PreprocessorInterface
{
    /**
     * @var FileDownloader
     */
    private FileDownloader $fileDownloader;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var ImageDirectoryProvider
     */
    private ImageDirectoryProvider $imageDirectoryProvider;

    /**
     * @var string|null
     */
    private ?string $downloadDir;

    /**
     * @var File
     */
    private File $file;

    /**
     * ImageDataPreprocessor constructor.
     *
     * @param Logger $logger
     * @param FileDownloader $fileDownloader
     * @param ImageDirectoryProvider $imageDirectoryProvider
     * @param File $file
     * @param string|null $downloadDir
     */
    public function __construct(
        Logger $logger,
        FileDownloader $fileDownloader,
        ImageDirectoryProvider $imageDirectoryProvider,
        File $file,
        ?string $downloadDir = null
    ) {
        $this->fileDownloader = $fileDownloader;
        $this->logger = $logger;
        $this->imageDirectoryProvider = $imageDirectoryProvider;
        $this->file = $file;
        $this->downloadDir = $downloadDir;
    }

    /**
     * @inheritDoc
     *
     * @param array $productData
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function process(array $productData): array
    {
//        $fileNames = $this->downloadImages($productData);
//
//        if (!empty($fileNames)) {
//            foreach ($fileNames as &$file) {
//                if (stristr($file, $this->getDownloadDir())) {
//                    $file = substr($file, strlen($this->getDownloadDir()) + 1);
//                }
//            }
//        }

//        $firstImage = array_shift($fileNames);
        $productData['base_image'] = '';
        $productData['small_image'] = '';
        $productData['thumbnail_image'] = '';
        $productData['swatch_image'] = '';
//        $productData['additional_images'] = empty($fileNames) ? null : implode(",", $fileNames);

        unset($productData['images']);
        return $productData;
    }

    /**
     * Download images
     *
     * @param array $productData
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function downloadImages(array $productData): array
    {
        $errors = [];
        $toDownload = [];
        $existingFiles = [];
        $downloadedFiles = [];

        if (empty($productData['images'])) {
            return [];
        }

        foreach ($productData['images'] as $image) {
            if (empty($image['Url'])) {
                continue;
            }

            $filePath = $this->getFilePath($image['Url']);

            if ($this->fileExists($filePath)) {
                $existingFiles[] = $filePath;
            } else {
                $toDownload[] = [
                    'file_path' => $this->getDownloadDir() . '/' . $filePath,
                    'url' => $image['Url']
                ];
            }
        }

        try {
            if (!empty($toDownload)) {
                [$downloadedFiles, $errors] = $this->fileDownloader->downloadBulk($toDownload);

                foreach ($errors as $e) {
                    $errors[] = $e;
                }
            }
        } catch (FileSystemException $e) {
            $errors[] = $e->getMessage();
        }

        $this->logErrors($errors);

        return array_merge($downloadedFiles, $existingFiles);
    }

    /**
     * Log errors
     *
     * @param array $errors
     * @return void
     */
    private function logErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $this->logger->error(
                'Error encountered while downloading images for katana import: ' . $error
            );
        }
    }

    /**
     * Get file path
     *
     * @param string $url
     * @return string
     */
    private function getFilePath(string $url): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $pathinfo = pathinfo($url);
        $fileName = $pathinfo['filename'] . '.' . $pathinfo['extension'];
        $correctName = Uploader::getCorrectFileName($fileName);

        $dispersion = ltrim(Uploader::getDispersionPath($correctName), '/');

        return $dispersion . '/' . $correctName;
    }

    /**
     * Get directory for file storage
     *
     * @return string
     * @throws FileSystemException
     */
    private function getDownloadDir(): string
    {
        try {
            if ($this->downloadDir === null) {
                $this->downloadDir = $this->imageDirectoryProvider->getDirectoryAbsolutePath();
            }

            return $this->downloadDir;
        } catch (LocalizedException $e) {
            throw new FileSystemException(
                __('Failed to create directory for downloading images. Error: %1', $e->getMessage()),
                $e
            );
        }
    }

    /**
     * Check if file already exists
     *
     * @param string $filePath
     * @return bool
     * @throws FileSystemException
     */
    private function fileExists(string $filePath): bool
    {
        $filePath = $this->getDownloadDir() . '/' . $filePath;

        return $this->file->fileExists($filePath);
    }
}
