<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use SimpleLicense\Vendor\Constants;
use SimpleLicense\Vendor\Resources\License;
use SimpleLicense\Vendor\Tests\Factories\LicenseFactory;
use SimpleLicense\Vendor\Tests\TestingConstants;

/**
 * License Resource Unit Tests
 */
class LicenseTest extends TestCase
{
    public function testFromArray(): void
    {
        // Arrange
        $data = [
            'license_key' => TestingConstants::TEST_LICENSE_KEY,
            'status' => Constants::LICENSE_STATUS_ACTIVE,
            'customer_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
            'tier_code' => TestingConstants::TEST_TIER_CODE,
            'domain' => TestingConstants::TEST_DOMAIN,
            'activation_limit' => TestingConstants::TEST_ACTIVATION_LIMIT,
            'activation_count' => TestingConstants::TEST_ACTIVATION_COUNT,
            'id' => TestingConstants::TEST_LICENSE_ID,
        ];

        // Act
        $license = License::fromArray($data);

        // Assert
        $this->assertEquals(TestingConstants::TEST_LICENSE_KEY, $license->licenseKey);
        $this->assertEquals(Constants::LICENSE_STATUS_ACTIVE, $license->status);
        $this->assertEquals(TestingConstants::TEST_CUSTOMER_EMAIL, $license->customerEmail);
        $this->assertEquals(TestingConstants::TEST_TIER_CODE, $license->tierCode);
        $this->assertEquals(TestingConstants::TEST_DOMAIN, $license->domain);
        $this->assertEquals(TestingConstants::TEST_ACTIVATION_LIMIT, $license->activationLimit);
        $this->assertEquals(TestingConstants::TEST_ACTIVATION_COUNT, $license->activationCount);
        $this->assertEquals(TestingConstants::TEST_LICENSE_ID, $license->id);
    }

    public function testToArray(): void
    {
        // Arrange
        $license = LicenseFactory::make();

        // Act
        $array = $license->toArray();

        // Assert
        $this->assertIsArray($array);
        $this->assertEquals(TestingConstants::TEST_LICENSE_KEY, $array['license_key']);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('customer_email', $array);
    }

    public function testFactoryActive(): void
    {
        // Arrange & Act
        $license = LicenseFactory::active();

        // Assert
        $this->assertEquals(Constants::LICENSE_STATUS_ACTIVE, $license->status);
    }

    public function testFactoryExpired(): void
    {
        // Arrange & Act
        $license = LicenseFactory::expired();

        // Assert
        $this->assertEquals(Constants::LICENSE_STATUS_EXPIRED, $license->status);
    }
}

