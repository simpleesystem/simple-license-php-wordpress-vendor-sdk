<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor;

use SimpleLicense\Vendor\Constants;
use SimpleLicense\Vendor\Exceptions\ApiException;
use SimpleLicense\Vendor\Exceptions\AuthenticationException;
use SimpleLicense\Vendor\Exceptions\LicenseNotFoundException;
use SimpleLicense\Vendor\Exceptions\NetworkException;
use SimpleLicense\Vendor\Exceptions\ValidationException;
use SimpleLicense\Vendor\Http\GuzzleHttpClient;
use SimpleLicense\Vendor\Http\HttpClientInterface;

/**
 * Main Client for Vendor SDK
 * Handles authentication and all admin API endpoints
 */
class Client
{
    private HttpClientInterface $httpClient;
    private string $baseUrl;
    private ?string $token = null;
    private ?int $tokenExpiresAt = null;

    public function __construct(
        string $baseUrl,
        ?HttpClientInterface $httpClient = null,
        int $timeout = Constants::DEFAULT_TIMEOUT_SECONDS
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($this->baseUrl, $timeout);
    }

    /**
     * Authenticate with username and password
     *
     * @param string $username Username
     * @param string $password Password
     * @return array<string, mixed> Authentication response
     * @throws AuthenticationException
     */
    public function authenticate(string $username, string $password): array
    {
        try {
            $response = $this->httpClient->post(Constants::API_ENDPOINT_AUTH_LOGIN, [
                'username' => $username,
                'password' => $password,
            ]);

            $data = $this->parseResponse($response);

            if (!isset($data[Constants::RESPONSE_KEY_SUCCESS]) || !$data[Constants::RESPONSE_KEY_SUCCESS]) {
                throw new AuthenticationException(
                    $data[Constants::RESPONSE_KEY_ERROR][Constants::RESPONSE_KEY_MESSAGE] ?? 'Authentication failed',
                    $data[Constants::RESPONSE_KEY_ERROR][Constants::RESPONSE_KEY_CODE] ?? Constants::ERROR_CODE_AUTHENTICATION_ERROR
                );
            }

            $this->token = $data[Constants::RESPONSE_KEY_TOKEN] ?? null;
            $expiresIn = $data[Constants::RESPONSE_KEY_EXPIRES_IN] ?? 0;
            $this->tokenExpiresAt = time() + $expiresIn;

            return $data;
        } catch (NetworkException $e) {
            throw new AuthenticationException('Network error during authentication', Constants::ERROR_CODE_AUTHENTICATION_ERROR, null, 0, $e);
        }
    }

    /**
     * Set authentication token directly
     *
     * @param string $token JWT token
     * @param int|null $expiresAt Unix timestamp when token expires
     */
    public function setToken(string $token, ?int $expiresAt = null): void
    {
        $this->token = $token;
        $this->tokenExpiresAt = $expiresAt;
    }

    /**
     * Get authentication headers
     *
     * @return array<string, string>
     * @throws AuthenticationException
     */
    private function getAuthHeaders(): array
    {
        if ($this->token === null) {
            throw new AuthenticationException('Not authenticated. Call authenticate() first.', Constants::ERROR_CODE_MISSING_TOKEN);
        }

        if ($this->tokenExpiresAt !== null && $this->tokenExpiresAt < time()) {
            throw new AuthenticationException('Token has expired. Please re-authenticate.', Constants::ERROR_CODE_INVALID_TOKEN);
        }

        return [
            Constants::HEADER_AUTHORIZATION => Constants::HEADER_BEARER_PREFIX . $this->token,
        ];
    }

    /**
     * Create a license
     *
     * @param array<string, mixed> $data License data
     * @return array<string, mixed> Created license
     * @throws ApiException
     */
    public function createLicense(array $data): array
    {
        $response = $this->makeRequest('POST', Constants::API_ENDPOINT_LICENSES_CREATE, $data);
        return $this->parseResponse($response)[Constants::RESPONSE_KEY_DATA] ?? [];
    }

    /**
     * List licenses
     *
     * @param array<string, mixed> $filters Optional filters (status, limit, offset)
     * @return array<string, mixed> List of licenses
     * @throws ApiException
     */
    public function listLicenses(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $url = Constants::API_ENDPOINT_LICENSES_LIST . ($queryString ? '?' . $queryString : '');
        $response = $this->makeRequest('GET', $url);
        return $this->parseResponse($response)[Constants::RESPONSE_KEY_DATA] ?? [];
    }

    /**
     * Get license by ID or key
     *
     * @param string $id License ID or key
     * @return array<string, mixed> License data
     * @throws LicenseNotFoundException
     * @throws ApiException
     */
    public function getLicense(string $id): array
    {
        $url = sprintf(Constants::API_ENDPOINT_LICENSES_GET, $id);
        $response = $this->makeRequest('GET', $url);
        $parsed = $this->parseResponse($response);

        if (!isset($parsed[Constants::RESPONSE_KEY_SUCCESS]) || !$parsed[Constants::RESPONSE_KEY_SUCCESS]) {
            $errorCode = $parsed[Constants::RESPONSE_KEY_ERROR][Constants::RESPONSE_KEY_CODE] ?? '';
            if ($errorCode === Constants::ERROR_CODE_LICENSE_NOT_FOUND) {
                throw new LicenseNotFoundException(
                    $parsed[Constants::RESPONSE_KEY_ERROR][Constants::RESPONSE_KEY_MESSAGE] ?? 'License not found',
                    $errorCode
                );
            }
        }

        return $parsed[Constants::RESPONSE_KEY_DATA] ?? [];
    }

    /**
     * Update a license
     *
     * @param string $id License ID or key
     * @param array<string, mixed> $data Update data
     * @return array<string, mixed> Updated license
     * @throws ApiException
     */
    public function updateLicense(string $id, array $data): array
    {
        $url = sprintf(Constants::API_ENDPOINT_LICENSES_UPDATE, $id);
        $response = $this->makeRequest('PUT', $url, $data);
        return $this->parseResponse($response)[Constants::RESPONSE_KEY_DATA] ?? [];
    }

    /**
     * Suspend a license
     *
     * @param string $id License ID or key
     * @return array<string, mixed> Response
     * @throws ApiException
     */
    public function suspendLicense(string $id): array
    {
        $url = sprintf(Constants::API_ENDPOINT_LICENSES_SUSPEND, $id);
        $response = $this->makeRequest('POST', $url);
        return $this->parseResponse($response);
    }

    /**
     * Resume a license
     *
     * @param string $id License ID or key
     * @return array<string, mixed> Response
     * @throws ApiException
     */
    public function resumeLicense(string $id): array
    {
        $url = sprintf(Constants::API_ENDPOINT_LICENSES_RESUME, $id);
        $response = $this->makeRequest('POST', $url);
        return $this->parseResponse($response);
    }

    /**
     * Revoke a license
     *
     * @param string $id License ID or key
     * @return array<string, mixed> Response
     * @throws ApiException
     */
    public function revokeLicense(string $id): array
    {
        $url = sprintf(Constants::API_ENDPOINT_LICENSES_REVOKE, $id);
        $response = $this->makeRequest('DELETE', $url);
        return $this->parseResponse($response);
    }

    /**
     * Get license activations
     *
     * @param string $id License ID or key
     * @return array<string, mixed> List of activations
     * @throws ApiException
     */
    public function getLicenseActivations(string $id): array
    {
        $url = sprintf(Constants::API_ENDPOINT_LICENSES_ACTIVATIONS, $id);
        $response = $this->makeRequest('GET', $url);
        return $this->parseResponse($response)[Constants::RESPONSE_KEY_DATA] ?? [];
    }

    /**
     * List products
     *
     * @return array<string, mixed> List of products
     * @throws ApiException
     */
    public function listProducts(): array
    {
        $response = $this->makeRequest('GET', Constants::API_ENDPOINT_PRODUCTS_LIST);
        return $this->parseResponse($response)[Constants::RESPONSE_KEY_DATA] ?? [];
    }

    /**
     * Get product by ID
     *
     * @param int|string $id Product ID
     * @return array<string, mixed> Product data
     * @throws ApiException
     */
    public function getProduct(int|string $id): array
    {
        $url = sprintf(Constants::API_ENDPOINT_PRODUCTS_GET, (string) $id);
        $response = $this->makeRequest('GET', $url);
        return $this->parseResponse($response)[Constants::RESPONSE_KEY_DATA] ?? [];
    }

    /**
     * Make HTTP request with authentication
     *
     * @param string $method HTTP method
     * @param string $url URL
     * @param array<string, mixed> $data Request data
     * @return array{status: int, body: string, headers: array<string, string>}
     * @throws ApiException
     */
    private function makeRequest(string $method, string $url, array $data = []): array
    {
        $headers = $this->getAuthHeaders();

        return match ($method) {
            'GET' => $this->httpClient->get($url, $headers),
            'POST' => $this->httpClient->post($url, $data, $headers),
            'PUT' => $this->httpClient->put($url, $data, $headers),
            'DELETE' => $this->httpClient->delete($url, $headers),
            default => throw new ApiException("Unsupported HTTP method: {$method}", Constants::ERROR_CODE_VALIDATION_ERROR),
        };
    }

    /**
     * Parse API response
     *
     * @param array{status: int, body: string, headers: array<string, string>} $response HTTP response
     * @return array<string, mixed> Parsed response data
     * @throws ApiException
     */
    private function parseResponse(array $response): array
    {
        $statusCode = $response['status'];
        $body = $response['body'];

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                'Invalid JSON response from server',
                Constants::ERROR_CODE_VALIDATION_ERROR,
                ['body' => $body]
            );
        }

        if ($statusCode >= Constants::HTTP_BAD_REQUEST) {
            $errorCode = $data[Constants::RESPONSE_KEY_ERROR][Constants::RESPONSE_KEY_CODE] ?? Constants::ERROR_CODE_VALIDATION_ERROR;
            $errorMessage = $data[Constants::RESPONSE_KEY_ERROR][Constants::RESPONSE_KEY_MESSAGE] ?? 'API error';

            if ($statusCode === Constants::HTTP_UNAUTHORIZED || $statusCode === Constants::HTTP_FORBIDDEN) {
                throw new AuthenticationException($errorMessage, $errorCode);
            }

            if ($statusCode === Constants::HTTP_NOT_FOUND && $errorCode === Constants::ERROR_CODE_LICENSE_NOT_FOUND) {
                throw new LicenseNotFoundException($errorMessage, $errorCode);
            }

            if ($statusCode === Constants::HTTP_BAD_REQUEST) {
                throw new ValidationException($errorMessage, $errorCode);
            }

            throw new ApiException($errorMessage, $errorCode, $data[Constants::RESPONSE_KEY_ERROR] ?? null);
        }

        return $data;
    }
}

