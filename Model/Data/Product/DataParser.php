<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product;

use Itonomy\Katanapim\Model\Data\Product\DataParser\BasicDataParser;
use Itonomy\Katanapim\Model\Data\Product\DataParser\DataParserInterface;

class DataParser
{
    /**
     * @var DataParserInterface[]
     */
    private array $dataParsers;

    /**
     * DataParser constructor.
     *
     * @param DataParserInterface[] $dataParsers
     */
    public function __construct(
        array $dataParsers
    ) {
        $this->dataParsers = $dataParsers;
    }

    /**
     * Parse data coming from katana PIM API.
     *
     * @param array $data
     * @return array
     */
    public function parse(array $data): array
    {
        $parsedData = [];

        foreach ($this->dataParsers as $parser) {
            $parser->setParsedData($parsedData);
            $parserValues = [];

            foreach ($data as $item) {
                $itemData = $parser->parse($item);

                if (empty($itemData)) {
                    continue;
                }

                $parserValues[$item[BasicDataParser::ID_KEY]] = $itemData;
            }

            $parsedData = array_replace_recursive($parsedData, $parserValues);
        }

        return $parsedData;
    }
}
