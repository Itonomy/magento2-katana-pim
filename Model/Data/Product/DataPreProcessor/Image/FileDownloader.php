<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor\Image;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class for downloading images
 */
class FileDownloader
{
    /**
     * HTTP OK Response Code
     */
    public const HTTP_OK = 200;

    /**
     * @var File
     */
    private File $file;

    /**
     * @param File $file
     */
    public function __construct(
        File $file
    ) {
        $this->file = $file;
    }

    /**
     * Download files in bulk
     *
     * @param array $filesInfo
     * @return array
     * @throws FileSystemException
     */
    public function downloadBulk(array $filesInfo): array
    {
        $handles = [];
        $downloadedFiles = [];
        $errors = [];

        $multihandle = curl_multi_init();

        foreach ($filesInfo as $key => $fileInfo) {
            $filePath = $fileInfo['file_path'];

            if (!$this->file->checkAndCreateFolder(dirname($filePath))) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', dirname($filePath)));
            }

            $handles[$key]['url'] = $fileInfo['url'];
            $handles[$key]['curl'] = curl_init();
            $handles[$key]['file'] = fopen($filePath, "wb");
            $handles[$key]['file_path'] = $filePath;

            curl_setopt($handles[$key]['curl'], CURLOPT_URL, $fileInfo['url']);
            curl_setopt($handles[$key]['curl'], CURLOPT_HEADER, 0);
            curl_setopt($handles[$key]['curl'], CURLOPT_FILE, $handles[$key]['file']);
            curl_setopt($handles[$key]['curl'], CURLOPT_TIMEOUT, 10);

            curl_multi_add_handle($multihandle, $handles[$key]['curl']);
        }

        $process = null;

        do {
            curl_multi_exec($multihandle, $process);
            usleep(1000);
        } while ($process > 0);

        foreach ($handles as $key => $handle) {
            $response = curl_getinfo($handle['curl'], CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multihandle, $handle['curl']);
            fclose($handle['file']);

            if ($response === self::HTTP_OK) {
                $downloadedFiles[$key] = $handle['file_path'];
            } else {
                $errors[] = 'Could not download file from ' . $handle['url'] . ' HTTP Response code: ' . $response;

                if (file_exists($handle['file_path'])) {
                    unlink($handle['file_path']);
                }
            }
        }

        curl_multi_close($multihandle);

        return [$downloadedFiles, $errors];
    }
}
