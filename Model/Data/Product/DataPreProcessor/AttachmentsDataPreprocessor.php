<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataPreProcessor;

class AttachmentsDataPreprocessor implements PreprocessorInterface
{
    /**
     * Default category name
     */
    private const KATANA_ATTACHMENTS = 'katana_attachments';

    /**
     * @inheritDoc
     *
     * @param array $productData
     * @return mixed
     */
    public function process(array $productData): array
    {
        $productData[self::KATANA_ATTACHMENTS] = $this->processAttachments($productData);

        return $productData;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return [];
    }

    /**
     * Process katana product attachments
     *
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
