<?php

declare(strict_types=1);

namespace SimpleLicense\Vendor\Tests\Factories;

use Faker\Factory as FakerFactory;
use SimpleLicense\Vendor\Resources\License;
use SimpleLicense\Vendor\Tests\TestingConstants;

/**
 * License Factory
 * Creates test License objects using faker and constants
 */
class LicenseFactory
{
    private static \Faker\Generator $faker;

    public static function make(array $overrides = []): License
    {
        if (!isset(self::$faker)) {
            self::$faker = FakerFactory::create();
        }

        $data = array_merge([
            'license_key' => TestingConstants::TEST_LICENSE_KEY,
            'status' => 'ACTIVE',
            'customer_email' => TestingConstants::TEST_CUSTOMER_EMAIL,
            'tier_code' => TestingConstants::TEST_TIER_CODE,
            'domain' => TestingConstants::TEST_DOMAIN,
            'activation_limit' => TestingConstants::TEST_ACTIVATION_LIMIT,
            'activation_count' => TestingConstants::TEST_ACTIVATION_COUNT,
            'expires_at' => self::$faker->dateTimeBetween('+1 month', '+1 year')->format('c'),
            'features' => [
                'max_sites' => TestingConstants::TEST_ACTIVATION_LIMIT,
                'support_level' => 'priority',
            ],
            'id' => TestingConstants::TEST_LICENSE_ID,
            'created_at' => self::$faker->dateTimeBetween('-1 year', 'now')->format('c'),
            'updated_at' => self::$faker->dateTimeBetween('-1 month', 'now')->format('c'),
        ], $overrides);

        return License::fromArray($data);
    }

    public static function active(): License
    {
        return self::make(['status' => 'ACTIVE']);
    }

    public static function expired(): License
    {
        return self::make([
            'status' => 'EXPIRED',
            'expires_at' => (new \DateTime('-1 day'))->format('c'),
        ]);
    }

    public static function revoked(): License
    {
        return self::make(['status' => 'REVOKED']);
    }
}

