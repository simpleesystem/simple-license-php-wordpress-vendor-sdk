<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor;

/**
 * Constants for Vendor SDK
 * All values MUST come from constants - zero hardcoded values allowed
 */
final class Constants
{
    // HTTP Status Codes
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_CONFLICT = 409;
    public const HTTP_LOCKED = 423;
    public const HTTP_TOO_MANY_REQUESTS = 429;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
    public const HTTP_BAD_GATEWAY = 502;
    public const HTTP_SERVICE_UNAVAILABLE = 503;

    // API Base Path
    public const API_BASE_PATH = '/api/v1';
    public const API_ADMIN_BASE_PATH = '/api/v1/admin';

    // Authentication Endpoints
    public const API_ENDPOINT_AUTH_LOGIN = '/api/v1/auth/login';

    // Admin License Endpoints
    public const API_ENDPOINT_LICENSES_LIST = '/api/v1/admin/licenses';
    public const API_ENDPOINT_LICENSES_CREATE = '/api/v1/admin/licenses/create';
    public const API_ENDPOINT_LICENSES_GET = '/api/v1/admin/licenses/%s';
    public const API_ENDPOINT_LICENSES_UPDATE = '/api/v1/admin/licenses/%s';
    public const API_ENDPOINT_LICENSES_SUSPEND = '/api/v1/admin/licenses/%s/suspend';
    public const API_ENDPOINT_LICENSES_RESUME = '/api/v1/admin/licenses/%s/resume';
    public const API_ENDPOINT_LICENSES_FREEZE = '/api/v1/admin/licenses/%s/freeze';
    public const API_ENDPOINT_LICENSES_REVOKE = '/api/v1/admin/licenses/%s';
    public const API_ENDPOINT_LICENSES_ACTIVATIONS = '/api/v1/admin/licenses/%s/activations';

    // Admin Product Endpoints
    public const API_ENDPOINT_PRODUCTS_LIST = '/api/v1/admin/products';
    public const API_ENDPOINT_PRODUCTS_CREATE = '/api/v1/admin/products';
    public const API_ENDPOINT_PRODUCTS_GET = '/api/v1/admin/products/%s';
    public const API_ENDPOINT_PRODUCTS_UPDATE = '/api/v1/admin/products/%s';
    public const API_ENDPOINT_PRODUCTS_DELETE = '/api/v1/admin/products/%s';
    public const API_ENDPOINT_PRODUCTS_SUSPEND = '/api/v1/admin/products/%s/suspend';
    public const API_ENDPOINT_PRODUCTS_RESUME = '/api/v1/admin/products/%s/resume';

    // License Status Values
    public const LICENSE_STATUS_ACTIVE = 'ACTIVE';
    public const LICENSE_STATUS_INACTIVE = 'INACTIVE';
    public const LICENSE_STATUS_EXPIRED = 'EXPIRED';
    public const LICENSE_STATUS_REVOKED = 'REVOKED';
    public const LICENSE_STATUS_SUSPENDED = 'SUSPENDED';

    // Activation Status Values
    public const ACTIVATION_STATUS_ACTIVE = 'ACTIVE';
    public const ACTIVATION_STATUS_INACTIVE = 'INACTIVE';
    public const ACTIVATION_STATUS_SUSPENDED = 'SUSPENDED';

    // Error Codes
    public const ERROR_CODE_INVALID_FORMAT = 'INVALID_FORMAT';
    public const ERROR_CODE_INVALID_LICENSE_FORMAT = 'INVALID_LICENSE_FORMAT';
    public const ERROR_CODE_LICENSE_NOT_FOUND = 'LICENSE_NOT_FOUND';
    public const ERROR_CODE_LICENSE_INACTIVE = 'LICENSE_INACTIVE';
    public const ERROR_CODE_LICENSE_EXPIRED = 'LICENSE_EXPIRED';
    public const ERROR_CODE_ACTIVATION_LIMIT_EXCEEDED = 'ACTIVATION_LIMIT_EXCEEDED';
    public const ERROR_CODE_NOT_ACTIVATED_ON_DOMAIN = 'NOT_ACTIVATED_ON_DOMAIN';
    public const ERROR_CODE_DEMO_MODE_MISMATCH = 'DEMO_MODE_MISMATCH';
    public const ERROR_CODE_VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const ERROR_CODE_BODY_VALIDATION_ERROR = 'BODY_VALIDATION_ERROR';
    public const ERROR_CODE_INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    public const ERROR_CODE_MUST_CHANGE_PASSWORD = 'MUST_CHANGE_PASSWORD';
    public const ERROR_CODE_MISSING_TOKEN = 'MISSING_TOKEN';
    public const ERROR_CODE_INVALID_TOKEN = 'INVALID_TOKEN';
    public const ERROR_CODE_UNAUTHORIZED = 'UNAUTHORIZED';
    public const ERROR_CODE_ENTITLEMENTS_FROZEN = 'ENTITLEMENTS_FROZEN';
    public const ERROR_CODE_TIER_FROZEN = 'TIER_FROZEN';
    public const ERROR_CODE_LICENSE_SUSPENDED = 'LICENSE_SUSPENDED';
    public const ERROR_CODE_PRODUCT_SUSPENDED = 'PRODUCT_SUSPENDED';
    public const ERROR_CODE_ACCOUNT_LOCKED = 'ACCOUNT_LOCKED';
    public const ERROR_CODE_TOO_MANY_ATTEMPTS = 'TOO_MANY_ATTEMPTS';
    public const ERROR_CODE_AUTHENTICATION_ERROR = 'AUTHENTICATION_ERROR';

    // HTTP Headers
    public const HEADER_AUTHORIZATION = 'Authorization';
    public const HEADER_CONTENT_TYPE = 'Content-Type';
    public const HEADER_ACCEPT = 'Accept';
    public const HEADER_BEARER_PREFIX = 'Bearer ';

    // Content Types
    public const CONTENT_TYPE_JSON = 'application/json';

    // Default Configuration Values
    public const DEFAULT_TIMEOUT_SECONDS = 30;
    public const DEFAULT_CONNECT_TIMEOUT_SECONDS = 10;
    public const DEFAULT_RETRY_ATTEMPTS = 3;
    public const DEFAULT_RETRY_DELAY_MS = 1000;

    // Validation Limits
    public const VALIDATION_DOMAIN_MAX_LENGTH = 255;
    public const VALIDATION_EMAIL_MAX_LENGTH = 255;
    public const VALIDATION_SITE_NAME_MAX_LENGTH = 255;
    public const VALIDATION_VERSION_MAX_LENGTH = 50;
    public const VALIDATION_ACTIVATION_LIMIT_MAX = 1000;
    public const VALIDATION_EXPIRES_DAYS_MAX = 36500;

    // License Key Pattern (Ed25519 format: payload.signature)
    public const LICENSE_KEY_PATTERN = '/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/';
    public const LICENSE_KEY_LENGTH = 1000;

    // Response Keys
    public const RESPONSE_KEY_SUCCESS = 'success';
    public const RESPONSE_KEY_DATA = 'data';
    public const RESPONSE_KEY_ERROR = 'error';
    public const RESPONSE_KEY_CODE = 'code';
    public const RESPONSE_KEY_MESSAGE = 'message';
    public const RESPONSE_KEY_TOKEN = 'token';
    public const RESPONSE_KEY_TOKEN_TYPE = 'token_type';
    public const RESPONSE_KEY_EXPIRES_IN = 'expires_in';

    // WooCommerce Integration Constants
    public const WOOCOMMERCE_ORDER_META_LICENSE_KEY = '_sls_license_key';
    public const WOOCOMMERCE_ORDER_META_LICENSE_STATUS = '_sls_license_status';
    public const WOOCOMMERCE_ORDER_META_LICENSE_ID = '_sls_license_id';

    // Private constructor to prevent instantiation
    private function __construct()
    {
    }
}

