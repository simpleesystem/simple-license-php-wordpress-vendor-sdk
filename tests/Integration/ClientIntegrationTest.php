<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SimpleLicense\Vendor\Client;
use SimpleLicense\Vendor\Constants;
use SimpleLicense\Vendor\Exceptions\AuthenticationException;
use SimpleLicense\Vendor\Exceptions\LicenseNotFoundException;
use SimpleLicense\Vendor\Exceptions\ValidationException;
use SimpleLicense\Vendor\Http\GuzzleHttpClient;
use SimpleLicense\Vendor\Resources\License;
use SimpleLicense\Vendor\Tests\TestingConstants;

/**
 * Client Integration Tests
 * Tests actual API workflows with mock HTTP server
 */
class ClientIntegrationTest extends TestCase
{
    private Client $client;
    private string $mockServerUrl;

    protected function setUp(): void
    {
        // In a real scenario, you'd use a mock HTTP server like WireMock or a simple PHP server
        // For this test, we'll use a mock HTTP client that simulates server responses
        $this->mockServerUrl = TestingConstants::TEST_API_BASE_URL;
        $this->client = new Client($this->mockServerUrl);
    }

    public function testCompleteLicenseLifecycle(): void
    {
        // Test the complete workflow: authenticate -> create -> get -> update -> revoke
        // This tests that the SDK correctly handles the full license management workflow

        // Arrange: Mock successful authentication
        $mockHttpClient = $this->createMockHttpClient([
            // Authentication
            [
                'method' => 'POST',
                'url' => Constants::API_ENDPOINT_AUTH_LOGIN,
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            'token' => TestingConstants::TEST_TOKEN,
                            'token_type' => 'Bearer',
                            'expires_in' => TestingConstants::TEST_TOKEN_EXPIRES_IN,
                        ],
                    ]),
                ],
            ],
            // Create license
            [
                'method' => 'POST',
                'url' => Constants::API_ENDPOINT_LICENSES_CREATE,
                'response' => [
                    'status' => Constants::HTTP_CREATED,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            'license_key' => TestingConstants::TEST_LICENSE_KEY,
                            'status' => Constants::LICENSE_STATUS_ACTIVE,
                            'customer_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
                            'tier_code' => TestingConstants::TEST_TIER_CODE,
                            'activation_limit' => TestingConstants::TEST_ACTIVATION_LIMIT,
                        ],
                    ]),
                ],
            ],
            // Get license
            [
                'method' => 'GET',
                'url' => sprintf(Constants::API_ENDPOINT_LICENSES_GET, TestingConstants::TEST_LICENSE_KEY),
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            'license_key' => TestingConstants::TEST_LICENSE_KEY,
                            'status' => Constants::LICENSE_STATUS_ACTIVE,
                        ],
                    ]),
                ],
            ],
            // Update license
            [
                'method' => 'PUT',
                'url' => sprintf(Constants::API_ENDPOINT_LICENSES_UPDATE, TestingConstants::TEST_LICENSE_KEY),
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            'license_key' => TestingConstants::TEST_LICENSE_KEY,
                            'status' => Constants::LICENSE_STATUS_ACTIVE,
                            'activation_limit' => TestingConstants::TEST_ACTIVATION_LIMIT + 1,
                        ],
                    ]),
                ],
            ],
            // Revoke license
            [
                'method' => 'DELETE',
                'url' => sprintf(Constants::API_ENDPOINT_LICENSES_REVOKE, TestingConstants::TEST_LICENSE_KEY),
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                    ]),
                ],
            ],
        ]);

        $client = new Client($this->mockServerUrl, $mockHttpClient);

        // Act: Execute complete workflow
        $authResult = $client->authenticate(TestingConstants::TEST_USERNAME, TestingConstants::TEST_PASSWORD);
        $this->assertTrue($authResult['success'], 'Authentication should succeed');

        $license = $client->createLicense([
            'customer_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
            'product_slug' => TestingConstants::TEST_PRODUCT_SLUG,
            'tier_code' => TestingConstants::TEST_TIER_CODE,
        ]);
        $this->assertEquals(TestingConstants::TEST_LICENSE_KEY, $license['license_key'], 'Created license should have correct key');
        $this->assertEquals(Constants::LICENSE_STATUS_ACTIVE, $license['status'], 'Created license should be active');

        $retrievedLicense = $client->getLicense(TestingConstants::TEST_LICENSE_KEY);
        $this->assertEquals(TestingConstants::TEST_LICENSE_KEY, $retrievedLicense['license_key'], 'Retrieved license should match');

        $updatedLicense = $client->updateLicense(TestingConstants::TEST_LICENSE_KEY, [
            'activation_limit' => TestingConstants::TEST_ACTIVATION_LIMIT + 1,
        ]);
        $this->assertEquals(TestingConstants::TEST_ACTIVATION_LIMIT + 1, $updatedLicense['activation_limit'], 'License should be updated');

        $revokeResult = $client->revokeLicense(TestingConstants::TEST_LICENSE_KEY);
        $this->assertTrue($revokeResult['success'], 'License revocation should succeed');
    }

    public function testAuthenticationTokenExpiration(): void
    {
        // Test that expired tokens are properly detected and rejected
        // This validates the SDK correctly handles token expiration

        $mockHttpClient = $this->createMockHttpClient([]);
        $client = new Client($this->mockServerUrl, $mockHttpClient);
        $expiredTime = time() - 1; // Token expired 1 second ago
        $client->setToken(TestingConstants::TEST_TOKEN, $expiredTime);

        // Act & Assert: Should throw AuthenticationException when token is expired
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token has expired');
        $client->getLicense(TestingConstants::TEST_LICENSE_KEY);
    }

    public function testLicenseNotFoundErrorHandling(): void
    {
        // Test that the SDK correctly identifies and throws LicenseNotFoundException
        // This validates proper error type handling

        $mockHttpClient = $this->createMockHttpClient([
            [
                'method' => 'POST',
                'url' => Constants::API_ENDPOINT_AUTH_LOGIN,
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            'token' => TestingConstants::TEST_TOKEN,
                            'expires_in' => TestingConstants::TEST_TOKEN_EXPIRES_IN,
                        ],
                    ]),
                ],
            ],
            [
                'method' => 'GET',
                'url' => sprintf(Constants::API_ENDPOINT_LICENSES_GET, 'nonexistent-key'),
                'response' => [
                    'status' => Constants::HTTP_NOT_FOUND,
                    'body' => json_encode([
                        'success' => false,
                        'error' => [
                            'code' => Constants::ERROR_CODE_LICENSE_NOT_FOUND,
                            'message' => 'License not found',
                        ],
                    ]),
                ],
            ],
        ]);

        $client = new Client($this->mockServerUrl, $mockHttpClient);
        $client->authenticate(TestingConstants::TEST_USERNAME, TestingConstants::TEST_PASSWORD);

        // Act & Assert
        $this->expectException(LicenseNotFoundException::class);
        $this->expectExceptionMessage('License not found');
        $client->getLicense('nonexistent-key');
    }

    public function testValidationErrorHandling(): void
    {
        // Test that validation errors are properly identified and thrown
        // This validates the SDK correctly handles API validation errors

        $mockHttpClient = $this->createMockHttpClient([
            [
                'method' => 'POST',
                'url' => Constants::API_ENDPOINT_AUTH_LOGIN,
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            'token' => TestingConstants::TEST_TOKEN,
                            'expires_in' => TestingConstants::TEST_TOKEN_EXPIRES_IN,
                        ],
                    ]),
                ],
            ],
            [
                'method' => 'POST',
                'url' => Constants::API_ENDPOINT_LICENSES_CREATE,
                'response' => [
                    'status' => Constants::HTTP_BAD_REQUEST,
                    'body' => json_encode([
                        'success' => false,
                        'error' => [
                            'code' => Constants::ERROR_CODE_VALIDATION_ERROR,
                            'message' => 'Invalid customer email',
                        ],
                    ]),
                ],
            ],
        ]);

        $client = new Client($this->mockServerUrl, $mockHttpClient);
        $client->authenticate(TestingConstants::TEST_USERNAME, TestingConstants::TEST_PASSWORD);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid customer email');
        $client->createLicense(['invalid' => 'data']);
    }

    public function testListLicensesWithFilters(): void
    {
        // Test that listLicenses correctly applies filters and returns filtered results
        // This validates the SDK correctly handles query parameters

        $mockHttpClient = $this->createMockHttpClient([
            [
                'method' => 'POST',
                'url' => Constants::API_ENDPOINT_AUTH_LOGIN,
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            'token' => TestingConstants::TEST_TOKEN,
                            'expires_in' => TestingConstants::TEST_TOKEN_EXPIRES_IN,
                        ],
                    ]),
                ],
            ],
            [
                'method' => 'GET',
                'url' => Constants::API_ENDPOINT_LICENSES_LIST . '?status=' . Constants::LICENSE_STATUS_ACTIVE . '&limit=10',
                'response' => [
                    'status' => Constants::HTTP_OK,
                    'body' => json_encode([
                        'success' => true,
                        'data' => [
                            [
                                'license_key' => TestingConstants::TEST_LICENSE_KEY,
                                'status' => Constants::LICENSE_STATUS_ACTIVE,
                            ],
                        ],
                    ]),
                ],
            ],
        ]);

        $client = new Client($this->mockServerUrl, $mockHttpClient);
        $client->authenticate(TestingConstants::TEST_USERNAME, TestingConstants::TEST_PASSWORD);

        // Act
        $licenses = $client->listLicenses([
            'status' => Constants::LICENSE_STATUS_ACTIVE,
            'limit' => 10,
        ]);

        // Assert
        $this->assertIsArray($licenses, 'Should return array of licenses');
        $this->assertCount(1, $licenses, 'Should return filtered licenses');
        $this->assertEquals(Constants::LICENSE_STATUS_ACTIVE, $licenses[0]['status'], 'Should only return active licenses');
    }

    /**
     * Create mock HTTP client that responds to specific requests
     *
     * @param array<int, array<string, mixed>> $responses Array of expected requests and responses
     * @return \SimpleLicense\Vendor\Http\HttpClientInterface
     */
    private function createMockHttpClient(array $responses): \SimpleLicense\Vendor\Http\HttpClientInterface
    {
        $mockClient = $this->createMock(\SimpleLicense\Vendor\Http\HttpClientInterface::class);

        if (empty($responses)) {
            return $mockClient;
        }

        // PHPUnit 12: Set up expectations - they'll match in call order
        // Store responses indexed by method+url for lookup
        $responseMap = [];
        foreach ($responses as $responseConfig) {
            $method = strtolower($responseConfig['method']);
            $url = $responseConfig['url'];
            $key = $method . ':' . $url;
            $responseMap[$key] = $responseConfig['response'];
        }

        // Set up expectations for each unique method
        $methodGroups = [];
        foreach ($responses as $responseConfig) {
            $method = strtolower($responseConfig['method']);
            if (!isset($methodGroups[$method])) {
                $methodGroups[$method] = [];
            }
            $methodGroups[$method][] = $responseConfig;
        }

        foreach ($methodGroups as $method => $configs) {
            $urls = array_column($configs, 'url');
            $responsesForMethod = array_map(function ($config) {
                return $config['response'];
            }, $configs);

            $invocation = $mockClient
                ->expects($this->exactly(count($configs)))
                ->method($method);

            if ($method === 'get' || $method === 'delete') {
                $invocation->with($this->callback(function ($actualUrl) use ($urls) {
                    static $callIndex = 0;
                    $expectedUrl = $urls[$callIndex] ?? '';
                    $callIndex++;
                    return str_contains($actualUrl, $expectedUrl);
                }));
            } else {
                $invocation->with(
                    $this->callback(function ($actualUrl) use ($urls) {
                        static $callIndex = 0;
                        $expectedUrl = $urls[$callIndex] ?? '';
                        $callIndex++;
                        return str_contains($actualUrl, $expectedUrl);
                    }),
                    $this->anything()
                );
            }

            $invocation->willReturnOnConsecutiveCalls(...$responsesForMethod);
        }

        return $mockClient;
    }
}

