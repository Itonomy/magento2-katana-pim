<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model\Data\Product\DataParser;

use Itonomy\Katanapim\Setup\Patch\Data\AddKatanaPimProductIdAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Api\WebsiteRepositoryInterface;

class ScopeDataParser implements DataParserInterface
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var WebsiteRepositoryInterface
     */
    private WebsiteRepositoryInterface $websiteRepository;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $katanaPimIdAttributeId;

    /**
     * @var string
     */
    private $defaultWebsiteCode;

    /**
     * @param ResourceConnection $resourceConnection
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @var array
     */
    public array $parsedData = [];

    /**
     * @inheritDoc
     *
     * @param array $data
     * @return array
     */
    public function parse(array $data): array
    {
        return $this->parseData($data);
    }

    /**
     * Parse data
     *
     * TODO: Add a proper implementation which retrieves some mapping or something from katanapim for this purpose.
     *  And remove this fix which only udates first-time-products.
     *
     * @param array $item
     * @return array
     * @throws \Exception
     */
    protected function parseData(array $item): array
    {
        $output = [];

        $katanaId = $item['Id'];
        $exists = $this->getIsProductExists($katanaId);

        if ($exists) {
            $output['product_websites'] = null;
        } else {
            $output['product_websites'] = $this->getDefaultWebsiteCode();
        }

        return $output;
    }

    /**
     * @inheritDoc
     *
     * @param array $parsedData
     * @return $thisget
     */
    public function setParsedData(array $parsedData): ScopeDataParser
    {
        $this->parsedData = $parsedData;
        return $this;
    }

    /**
     * Check whether product already exists
     *
     * @param int $katanaId
     * @return bool
     * @throws \Exception
     */
    private function getIsProductExists(int $katanaId): bool
    {
        $connection = $this->getConnection();

        $attributeId = $this->getKatanaPimAttributeId($connection);

        $select = $connection->select()->from(
            ['cpei' => $connection->getTableName('catalog_product_entity_int')],
            ['value']
        )
            ->where('cpei.attribute_id = ?', $attributeId)
            ->where('cpei.value = ?', $katanaId);

        return (bool)$connection->fetchOne($select);
    }

    /**
     * Get price attribute id
     *
     * @param AdapterInterface $connection
     * @return string
     * @throws \Exception
     */
    private function getKatanaPimAttributeId(AdapterInterface $connection): string
    {
        if (!isset($this->katanaPimIdAttributeId)) {
            $select = $connection->select()->from(
                ['ea' => $connection->getTableName('eav_attribute')],
                'attribute_id'
            )->where('attribute_code = ?', AddKatanaPimProductIdAttribute::KATANA_PRODUCT_ID_ATTRIBUTE_CODE);

            $value = $connection->fetchOne($select);

            if (!is_string($value)) {
                throw new NotFoundException(__('Could not retrieve katana pim attribute id'));
            }

            $this->katanaPimIdAttributeId = $value;
        }

        return $this->katanaPimIdAttributeId;
    }

    /**
     * Get connections
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }

    /**
     * Get default website code
     *
     * @return string
     */
    private function getDefaultWebsiteCode(): string
    {
        if (null === $this->defaultWebsiteCode) {
            $this->defaultWebsiteCode = $this->websiteRepository->getDefault()->getCode();
        }

        return $this->defaultWebsiteCode;
    }
}
