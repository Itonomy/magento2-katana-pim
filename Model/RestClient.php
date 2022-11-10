<?php
declare(strict_types=1);

namespace Itonomy\Katanapim\Model;

use Itonomy\Katanapim\Model\Config\Katana as Config;
use Itonomy\Katanapim\Model\Exception\WebApiException;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\Http\ClientFactory;
use Laminas\Http\HeadersFactory;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use Magento\Framework\Serialize\Serializer\Json;

class RestClient
{

    /**
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * @var HeadersFactory
     */
    private HeadersFactory $headersFactory;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * RestClient constructor.
     *
     * @param Json $jsonSerializer
     * @param HeadersFactory $headersFactory
     * @param ClientFactory $clientFactory
     * @param Config $config
     */
    public function __construct(
        Json $jsonSerializer,
        HeadersFactory $headersFactory,
        ClientFactory $clientFactory,
        Config $config
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->headersFactory = $headersFactory;
        $this->clientFactory = $clientFactory;
        $this->config = $config;
    }

    /**
     * Execute a rest api call
     *
     * @param string $urlPart
     * @param string $method
     * @param Parameters|null $parameters
     * @return array|null
     * @throws WebApiException
     * @throws \InvalidArgumentException
     */
    public function execute(
        string $urlPart,
        string $method = Request::METHOD_GET,
        Parameters $parameters = null
    ): ?array {
        if (empty($this->config->getApiUrl())) {
            throw new \InvalidArgumentException('Undefined API base url.');
        }

        $url = $this->config->getApiUrl() . $urlPart;

        if ($parameters === null) {
            $parameters = new Parameters();
        }

        return $this->instantiateApiCall($url, $method, $parameters) ?? null;
    }

    /**
     * Instantiate a rest api call
     *
     * @param string $url
     * @param string $method
     * @param Parameters $parameters
     * @return ?array
     * @throws WebApiException
     */
    private function instantiateApiCall(
        string $url,
        string $method,
        Parameters $parameters
    ): ?array {
        $httpHeaders = $this->headersFactory->create();

        $httpHeaders->addHeaders([
            'Accept' => 'application/json',
            'apikey' => $this->config->getApiKey()
        ]);

        try {
            $client = $this->clientFactory->create();

            $client->setMethod($method);
            $client->setUri($url);
            $client->setHeaders($httpHeaders);

            if ($method === Request::METHOD_POST) {
                $client->setRawBody($this->jsonSerializer->serialize($parameters));
                $client->setEncType('application/json');
            } else {
                $client->setParameterGet($parameters->toArray());
            }

            $client->setOptions([
                'adapter' => Curl::class,
                'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
                'maxredirects' => 0,
                'timeout' => 180,
            ]);

            $response = $client->send();

            if ($response->getStatusCode() === 200) {
                return $this->jsonSerializer->unserialize($response->getBody());
            } else {
                throw new WebApiException(
                    'Unexpected response status code - '
                    . $response->getStatusCode()
                    . ' Body: ' . $response->getBody()
                );
            }
        } catch (\Exception $e) {
            throw new WebApiException(
                'Error encountered while trying to make a REST API call. ' . $e->getMessage()
            );
        }
    }
}
