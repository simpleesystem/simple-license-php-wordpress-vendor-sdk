# Simple License System - Vendor SDK

PHP SDK for WooCommerce vendors selling licenses via Simple License System.

## Installation

```bash
composer require simple-license/vendor-sdk
```

## Quick Start

```php
use SimpleLicense\Vendor\Client;
use SimpleLicense\Vendor\WooCommerceHelper;

// Initialize client
$client = new Client('https://your-license-server.com');

// Authenticate
$client->authenticate('username', 'password');

// Create a license
$license = $client->createLicense([
    'customer_email' => 'customer@example.com',
    'product_slug' => 'my-product',
    'tier_code' => '01',
    'domain' => 'example.com',
    'activation_limit' => 3,
]);

// WooCommerce Integration
$helper = new WooCommerceHelper($client);
$helper->registerOrderHooks();

// Create license from order
$license = $helper->createLicenseFromOrder($orderId, [
    'product_slug' => 'my-product',
    'tier_code' => '01',
]);

// License Management
$client->suspendLicense($licenseKey);
$client->resumeLicense($licenseKey);
$client->freezeLicense($licenseKey, [
    'freeze_entitlements' => true,
    'freeze_tier' => true,
]);
$activations = $client->getLicenseActivations($licenseKey);

// Product Management
$products = $client->listProducts();
$product = $client->createProduct([
    'name' => 'My Product',
    'prefix' => 'MP',
    'active' => true,
]);
$client->updateProduct($productId, ['name' => 'Updated Name']);
$client->suspendProduct($productId);
$client->resumeProduct($productId);
$client->deleteProduct($productId);
```

## API Coverage

### License Management
- `createLicense()` - Create a new license
- `listLicenses()` - List licenses with optional filters (status, limit, offset)
- `getLicense()` - Get license by ID or key
- `updateLicense()` - Update license properties
- `suspendLicense()` - Suspend a license
- `resumeLicense()` - Resume a suspended license
- `freezeLicense()` - Freeze license entitlements and/or tier changes
- `revokeLicense()` - Revoke a license permanently
- `getLicenseActivations()` - Get all activations for a license

### Product Management
- `listProducts()` - List all products (vendor-scoped)
- `getProduct()` - Get product by ID
- `createProduct()` - Create a new product
- `updateProduct()` - Update product properties
- `deleteProduct()` - Delete a product
- `suspendProduct()` - Suspend a product
- `resumeProduct()` - Resume a suspended product

## WooCommerce Integration

The SDK includes a `WooCommerceHelper` class for seamless WooCommerce integration:

```php
$helper = new WooCommerceHelper($client);

// Register automatic order fulfillment hooks
$helper->registerOrderHooks(function ($product, $item, $order) {
    // Map WooCommerce product to SLS product/tier
    return [
        'product_slug' => 'my-product',
        'tier_code' => '01',
    ];
});

// Manual license creation from order
$license = $helper->createLicenseFromOrder($orderId, $licenseData);

// Revoke license on refund/cancellation
$helper->revokeLicenseForOrder($orderId);

// Get license for order
$license = $helper->getLicenseForOrder($orderId);
```

## Error Handling

All methods throw typed exceptions:

- `ApiException` - Base exception for all API errors
- `AuthenticationException` - Authentication failures
- `ValidationException` - Request validation errors
- `LicenseNotFoundException` - License not found
- `NetworkException` - Network/HTTP errors

```php
try {
    $license = $client->createLicense($data);
} catch (AuthenticationException $e) {
    // Handle authentication error
} catch (ValidationException $e) {
    // Handle validation error
} catch (ApiException $e) {
    // Handle other API errors
}
```

## Configuration

```php
$client = new Client(
    baseUrl: 'https://your-license-server.com',
    httpClient: null, // Optional custom HTTP client
    timeout: 30 // Optional timeout in seconds
);
```

## Testing

```bash
# Run tests
composer test

# Run with coverage
composer test:coverage

# Mutation testing
composer test:mutation
```

## Requirements

- PHP 8.1+
- Guzzle HTTP Client 7.5+

## License

MIT

