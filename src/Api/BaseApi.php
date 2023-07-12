<?php

namespace Macellan\Netgsm\Api;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Macellan\Netgsm\Exceptions\HttpClientException;
use Macellan\Netgsm\Exceptions\NetgsmException;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;
use Throwable;

abstract class BaseApi
{
    public const BASE_URL = 'https://api.netgsm.com.tr';

    public function __construct(protected array $config)
    {
    }

    abstract protected function checkErrors(Response $response): void;

    protected function getUserName(): string
    {
        return $this->config['username'] ?? '';
    }

    protected function getPassword(): string
    {
        return $this->config['password'] ?? '';
    }

    protected function getMessageHeader(): string
    {
        return $this->config['header'] ?? '';
    }

    /**
     * @throws HttpClientException
     */
    protected function xmlRequest(string $path, array $data, string $rootElement): Response
    {
        try {
            $response = Http::timeout(10)
                ->withBody(
                    ArrayToXml::convert($data, $rootElement, true, 'UTF-8'),
                    'application/xml'
                )
                ->post(self::BASE_URL.$path)
                ->throw();
        } catch (Throwable $e) {
            throw new HttpClientException(trans('netgsm::errors.http_client_exception'), $e->getCode(), $e->getPrevious());
        }

        $this->checkErrors($response);

        return $response;
    }

    /**
     * @throws NetgsmException
     */
    protected function parseResponseToXml(Response $response): SimpleXMLElement
    {
        try {
            $xml = simplexml_load_string($response->body());
            if ($xml === false) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            throw new NetgsmException(
                sprintf('%s - Response: %s',
                    trans('netgsm::errors.xml_parse_error'),
                    $response->body()
                )
            );
        }

        return $xml;
    }
}
