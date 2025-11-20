<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use SimpleLicense\Vendor\Constants;
use SimpleLicense\Vendor\Exceptions\NetworkException;

/**
 * Guzzle HTTP Client Implementation
 */
class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClient $client;
    private int $timeout;
    private int $connectTimeout;

    public function __construct(
        string $baseUrl,
        int $timeout = Constants::DEFAULT_TIMEOUT_SECONDS,
        int $connectTimeout = Constants::DEFAULT_CONNECT_TIMEOUT_SECONDS
    ) {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->client = new GuzzleClient([
            'base_uri' => $baseUrl,
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
            'http_errors' => false, // We handle errors ourselves
        ]);
    }

    public function get(string $url, array $headers = []): array
    {
        return $this->request('GET', $url, ['headers' => $headers]);
    }

    public function post(string $url, array $data = [], array $headers = []): array
    {
        $options = [
            'headers' => array_merge(
                [Constants::HEADER_CONTENT_TYPE => Constants::CONTENT_TYPE_JSON],
                $headers
            ),
            'json' => $data,
        ];

        return $this->request('POST', $url, $options);
    }

    public function put(string $url, array $data = [], array $headers = []): array
    {
        $options = [
            'headers' => array_merge(
                [Constants::HEADER_CONTENT_TYPE => Constants::CONTENT_TYPE_JSON],
                $headers
            ),
            'json' => $data,
        ];

        return $this->request('PUT', $url, $options);
    }

    public function delete(string $url, array $headers = []): array
    {
        return $this->request('DELETE', $url, ['headers' => $headers]);
    }

    /**
     * Execute HTTP request
     *
     * @param string $method HTTP method
     * @param string $url URL
     * @param array<string, mixed> $options Request options
     * @return array{status: int, body: string, headers: array<string, string>}
     * @throws NetworkException
     */
    private function request(string $method, string $url, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);

            $responseHeaders = [];
            foreach ($response->getHeaders() as $name => $values) {
                $responseHeaders[$name] = implode(', ', $values);
            }

            return [
                'status' => $response->getStatusCode(),
                'body' => (string) $response->getBody(),
                'headers' => $responseHeaders,
            ];
        } catch (GuzzleException $e) {
            throw new NetworkException(
                sprintf('Network error: %s', $e->getMessage()),
                Constants::ERROR_CODE_AUTHENTICATION_ERROR,
                null,
                0,
                $e
            );
        }
    }
}

