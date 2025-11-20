<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Tests\Factories;

use SimpleLicense\Vendor\Tests\TestingConstants;

/**
 * Response Factory
 * Creates test API response arrays using constants
 */
class ResponseFactory
{
    public static function success(array $data = [], int $statusCode = TestingConstants::TEST_HTTP_STATUS_OK): array
    {
        return [
            'status' => $statusCode,
            'body' => json_encode([
                'success' => true,
                'data' => $data,
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }

    public static function error(
        string $message,
        string $code = TestingConstants::TEST_ERROR_CODE_VALIDATION,
        int $statusCode = TestingConstants::TEST_HTTP_STATUS_BAD_REQUEST
    ): array {
        return [
            'status' => $statusCode,
            'body' => json_encode([
                'success' => false,
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }

    public static function authenticationSuccess(string $token = TestingConstants::TEST_TOKEN): array
    {
        return self::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => TestingConstants::TEST_TOKEN_EXPIRES_IN,
            'user' => [
                'id' => 1,
                'username' => TestingConstants::TEST_USERNAME,
                'email' => TestingConstants::TEST_CUSTOMER_EMAIL,
                'role' => 'ADMIN',
            ],
        ]);
    }
}

