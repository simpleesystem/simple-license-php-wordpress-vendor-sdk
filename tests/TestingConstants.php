<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Tests;

/**
 * Testing Constants
 * All test data constants - zero hardcoded values in tests
 */
final class TestingConstants
{
    // Test API URLs
    public const TEST_API_BASE_URL = 'https://api.example.com';
    public const TEST_API_ENDPOINT_LOGIN = '/api/v1/auth/login';
    public const TEST_API_ENDPOINT_LICENSES = '/api/v1/admin/licenses';

    // Test License Data
    public const TEST_LICENSE_KEY = 'eyJ2ZXJzaW9uIjoyLCJwcm9kdWN0SWQiOjEsInRpZXJDb2RlIjoiMDEiLCJkb21haW4iOiJleGFtcGxlLmNvbSIsImRlbW9Nb2RlIjpmYWxzZX0.signature';
    public const TEST_LICENSE_ID = 1;
    public const TEST_CUSTOMER_EMAIL = 'test@example.com';
    public const TEST_DOMAIN = 'example.com';
    public const TEST_TIER_CODE = '01';
    public const TEST_ACTIVATION_LIMIT = 3;
    public const TEST_ACTIVATION_COUNT = 1;

    // Test Product Data
    public const TEST_PRODUCT_ID = 1;
    public const TEST_PRODUCT_NAME = 'Test Product';
    public const TEST_PRODUCT_SLUG = 'test-product';
    public const TEST_PRODUCT_PREFIX = 'TST';

    // Test Authentication
    public const TEST_USERNAME = 'testuser';
    public const TEST_PASSWORD = 'testpassword';
    public const TEST_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6InRlc3R1c2VyIn0.signature';
    public const TEST_TOKEN_EXPIRES_IN = 43200;

    // Test WooCommerce Data
    public const TEST_ORDER_ID = 123;
    public const TEST_WC_PRODUCT_ID = 456;

    // Test HTTP Status Codes
    public const TEST_HTTP_STATUS_OK = 200;
    public const TEST_HTTP_STATUS_CREATED = 201;
    public const TEST_HTTP_STATUS_BAD_REQUEST = 400;
    public const TEST_HTTP_STATUS_UNAUTHORIZED = 401;
    public const TEST_HTTP_STATUS_NOT_FOUND = 404;

    // Test Error Codes
    public const TEST_ERROR_CODE_NOT_FOUND = 'LICENSE_NOT_FOUND';
    public const TEST_ERROR_CODE_VALIDATION = 'VALIDATION_ERROR';
    public const TEST_ERROR_CODE_AUTH = 'AUTHENTICATION_ERROR';

    // Test Time Values
    public const TEST_TIMEOUT_SECONDS = 30;
    public const TEST_CONNECT_TIMEOUT_SECONDS = 10;

    // Private constructor to prevent instantiation
    private function __construct()
    {
    }
}

