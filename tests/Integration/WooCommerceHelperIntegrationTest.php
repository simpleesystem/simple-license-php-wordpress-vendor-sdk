<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SimpleLicense\Vendor\Client;
use SimpleLicense\Vendor\Constants;
use SimpleLicense\Vendor\Resources\License;
use SimpleLicense\Vendor\WooCommerceHelper;
use SimpleLicense\Vendor\Tests\TestingConstants;

/**
 * WooCommerce Helper Integration Tests
 * Tests WooCommerce integration workflows
 */
class WooCommerceHelperIntegrationTest extends TestCase
{
    private Client $client;
    private WooCommerceHelper $helper;

    protected function setUp(): void
    {
        // Note: These tests require WooCommerce to be available
        // In a real test environment, you'd use WordPress test framework
        if (!class_exists('WC_Order')) {
            $this->markTestSkipped('WooCommerce not available');
        }

        $this->client = $this->createMock(Client::class);
        $this->helper = new WooCommerceHelper($this->client);
    }

    public function testCreateLicenseFromOrderExtractsCustomerEmail(): void
    {
        // Test that createLicenseFromOrder correctly extracts customer email from order
        // This validates the helper correctly integrates with WooCommerce order data

        // Arrange
        $order = $this->createMockOrder([
            'billing_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
        ]);

        $expectedLicenseData = [
            'customer_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
            'product_slug' => TestingConstants::TEST_PRODUCT_SLUG,
            'tier_code' => TestingConstants::TEST_TIER_CODE,
        ];

        $license = License::fromArray([
            'license_key' => TestingConstants::TEST_LICENSE_KEY,
            'status' => Constants::LICENSE_STATUS_ACTIVE,
            'customer_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
        ]);

        $this->client
            ->expects($this->once())
            ->method('createLicense')
            ->with($this->callback(function ($data) use ($expectedLicenseData) {
                return $data['customer_email'] === $expectedLicenseData['customer_email'];
            }))
            ->willReturn($license->toArray());

        // Act
        $result = $this->helper->createLicenseFromOrder($order, [
            'product_slug' => TestingConstants::TEST_PRODUCT_SLUG,
            'tier_code' => TestingConstants::TEST_TIER_CODE,
        ]);

        // Assert
        $this->assertInstanceOf(License::class, $result, 'Should return License object');
        $this->assertEquals(TestingConstants::TEST_LICENSE_KEY, $result->licenseKey, 'Should have correct license key');
    }

    public function testCreateLicenseFromOrderStoresMetadata(): void
    {
        // Test that createLicenseFromOrder stores license metadata in order
        // This validates the helper correctly persists license information

        // Arrange
        $order = $this->createMockOrder([
            'billing_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
        ]);

        $license = License::fromArray([
            'license_key' => TestingConstants::TEST_LICENSE_KEY,
            'status' => Constants::LICENSE_STATUS_ACTIVE,
            'id' => TestingConstants::TEST_LICENSE_ID,
        ]);

        $this->client
            ->method('createLicense')
            ->willReturn($license->toArray());

        // Act
        $this->helper->createLicenseFromOrder($order, [
            'product_slug' => TestingConstants::TEST_PRODUCT_SLUG,
            'tier_code' => TestingConstants::TEST_TIER_CODE,
        ]);

        // Assert: Verify order metadata was stored
        $this->assertEquals(
            TestingConstants::TEST_LICENSE_KEY,
            $order->get_meta(Constants::WOOCOMMERCE_ORDER_META_LICENSE_KEY),
            'Order should store license key'
        );
        $this->assertEquals(
            Constants::LICENSE_STATUS_ACTIVE,
            $order->get_meta(Constants::WOOCOMMERCE_ORDER_META_LICENSE_STATUS),
            'Order should store license status'
        );
        $this->assertEquals(
            TestingConstants::TEST_LICENSE_ID,
            $order->get_meta(Constants::WOOCOMMERCE_ORDER_META_LICENSE_ID),
            'Order should store license ID'
        );
    }

    public function testRevokeLicenseForOrderHandlesMissingLicense(): void
    {
        // Test that revokeLicenseForOrder gracefully handles orders without licenses
        // This validates the helper doesn't throw errors for orders without licenses

        // Arrange
        $order = $this->createMockOrder([]);
        // Order has no license metadata

        $this->client
            ->expects($this->never())
            ->method('revokeLicense');

        // Act: Should not throw exception
        $this->helper->revokeLicenseForOrder($order);

        // Assert: No exception thrown
        $this->assertTrue(true);
    }

    public function testGetLicenseForOrderReturnsNullWhenNotFound(): void
    {
        // Test that getLicenseForOrder returns null for orders without licenses
        // This validates the helper correctly handles missing license data

        // Arrange
        $order = $this->createMockOrder([]);
        // Order has no license metadata

        // Act
        $result = $this->helper->getLicenseForOrder($order);

        // Assert
        $this->assertNull($result, 'Should return null when order has no license');
    }

    public function testGetLicenseForOrderRetrievesExistingLicense(): void
    {
        // Test that getLicenseForOrder correctly retrieves license for order
        // This validates the helper can fetch license data from the API

        // Arrange
        $order = $this->createMockOrder([]);
        $order->update_meta_data(Constants::WOOCOMMERCE_ORDER_META_LICENSE_KEY, TestingConstants::TEST_LICENSE_KEY);
        $order->update_meta_data(Constants::WOOCOMMERCE_ORDER_META_LICENSE_ID, TestingConstants::TEST_LICENSE_ID);

        $license = License::fromArray([
            'license_key' => TestingConstants::TEST_LICENSE_KEY,
            'status' => Constants::LICENSE_STATUS_ACTIVE,
        ]);

        $this->client
            ->expects($this->once())
            ->method('getLicense')
            ->with((string) TestingConstants::TEST_LICENSE_ID)
            ->willReturn($license->toArray());

        // Act
        $result = $this->helper->getLicenseForOrder($order);

        // Assert
        $this->assertInstanceOf(License::class, $result, 'Should return License object');
        $this->assertEquals(TestingConstants::TEST_LICENSE_KEY, $result->licenseKey, 'Should have correct license key');
    }

    /**
     * Create mock WooCommerce order
     *
     * @param array<string, mixed> $data Order data
     * @return \WC_Order
     */
    private function createMockOrder(array $data): \WC_Order
    {
        $order = $this->createMock(\WC_Order::class);

        if (isset($data['billing_email'])) {
            $order->method('get_billing_email')->willReturn($data['billing_email']);
        }

        // Mock meta data methods
        $metaData = [];
        $order->method('get_meta')->willReturnCallback(function ($key) use (&$metaData) {
            return $metaData[$key] ?? '';
        });
        $order->method('update_meta_data')->willReturnCallback(function ($key, $value) use (&$metaData) {
            $metaData[$key] = $value;
        });
        $order->method('save')->willReturn(true);

        return $order;
    }
}

