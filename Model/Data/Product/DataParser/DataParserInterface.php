<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataParser;

/**
 * Interface DataParserInterface
 */
interface DataParserInterface
{
    /**
     * Parse katana product data
     *
     * @param array $data
     * @return array
     */
    public function parse(array $data): array;

    /**
     * Set already parsed data
     *
     * @param array $parsedData
     * @return $this
     */
    public function setParsedData(array $parsedData): DataParserInterface;
}
