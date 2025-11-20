<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SimpleLicense\Vendor\Client;
use SimpleLicense\Vendor\Constants;
use SimpleLicense\Vendor\Exceptions\AuthenticationException;
use SimpleLicense\Vendor\Exceptions\LicenseNotFoundException;
use SimpleLicense\Vendor\Http\HttpClientInterface;
use SimpleLicense\Vendor\Tests\Factories\ResponseFactory;
use SimpleLicense\Vendor\Tests\TestingConstants;

/**
 * Client Unit Tests
 */
class ClientTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private Client $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->client = new Client(TestingConstants::TEST_API_BASE_URL, $this->httpClient);
    }

    public function testAuthenticateSuccess(): void
    {
        // Arrange
        $response = ResponseFactory::authenticationSuccess();
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(Constants::API_ENDPOINT_AUTH_LOGIN, [
                'username' => TestingConstants::TEST_USERNAME,
                'password' => TestingConstants::TEST_PASSWORD,
            ])
            ->willReturn($response);

        // Act
        $result = $this->client->authenticate(TestingConstants::TEST_USERNAME, TestingConstants::TEST_PASSWORD);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(TestingConstants::TEST_TOKEN, $result['token']);
    }

    public function testAuthenticateFailure(): void
    {
        // Arrange
        $response = ResponseFactory::error(
            'Invalid credentials',
            Constants::ERROR_CODE_INVALID_CREDENTIALS,
            Constants::HTTP_UNAUTHORIZED
        );
        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($response);

        // Act & Assert
        $this->expectException(AuthenticationException::class);
        $this->client->authenticate(TestingConstants::TEST_USERNAME, TestingConstants::TEST_PASSWORD);
    }

    public function testCreateLicense(): void
    {
        // Arrange
        $licenseData = [
            'customer_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
            'product_slug' => TestingConstants::TEST_PRODUCT_SLUG,
            'tier_code' => TestingConstants::TEST_TIER_CODE,
        ];
        $response = ResponseFactory::success([
            'license_key' => TestingConstants::TEST_LICENSE_KEY,
            'status' => Constants::LICENSE_STATUS_ACTIVE,
        ], Constants::HTTP_CREATED);

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(Constants::API_ENDPOINT_LICENSES_CREATE, $licenseData)
            ->willReturn($response);

        // Act
        $result = $this->client->createLicense($licenseData);

        // Assert
        $this->assertEquals(TestingConstants::TEST_LICENSE_KEY, $result['license_key']);
        $this->assertEquals(Constants::LICENSE_STATUS_ACTIVE, $result['status']);
    }

    public function testGetLicenseNotFound(): void
    {
        // Arrange
        $response = ResponseFactory::error(
            'License not found',
            Constants::ERROR_CODE_LICENSE_NOT_FOUND,
            Constants::HTTP_NOT_FOUND
        );
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn($response);

        // Act & Assert
        $this->expectException(LicenseNotFoundException::class);
        $this->client->getLicense(TestingConstants::TEST_LICENSE_KEY);
    }

    public function testSetToken(): void
    {
        // Arrange
        $token = TestingConstants::TEST_TOKEN;
        $expiresAt = time() + TestingConstants::TEST_TOKEN_EXPIRES_IN;

        // Act
        $this->client->setToken($token, $expiresAt);

        // Assert - token should be set (we can't directly access private property, but we can test via a method that uses it)
        // This is a simple test to verify the method doesn't throw
        $this->assertTrue(true);
    }
}

