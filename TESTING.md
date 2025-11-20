# Testing Guide

## Running Tests

### Prerequisites

- PHP 8.1 or higher
- Composer installed

### Installation

```bash
composer install
```

### Run All Tests

```bash
# Unit tests
vendor/bin/phpunit tests/Unit/

# Integration tests
vendor/bin/phpunit tests/Integration/

# All tests
vendor/bin/phpunit
```

### Run with Coverage

```bash
vendor/bin/phpunit --coverage-html coverage/html
```

### Run Mutation Testing

```bash
vendor/bin/infection
```

## Test Structure

### Unit Tests (`tests/Unit/`)
- Test individual classes in isolation
- Use mocks for dependencies
- Fast execution
- High coverage target: 95%+

### Integration Tests (`tests/Integration/`)
- Test complete workflows
- Test expected behavior, not just code paths
- Validate API contracts
- Test error handling scenarios

### Test Factories (`tests/Factories/`)
- Generate test data using Faker
- Use constants, no hardcoded values
- Reusable across tests

## Test Principles

1. **Test Expected Behavior**: Tests validate business logic and intended functionality
2. **No Hardcoded Values**: All test data comes from constants or factories
3. **Arrange-Act-Assert**: Clear test structure
4. **Complete Workflows**: Integration tests cover end-to-end scenarios
5. **Error Scenarios**: Tests verify proper exception handling

## Example Test Run

```bash
$ vendor/bin/phpunit --testdox

SimpleLicense\Vendor\Tests\Unit\ClientTest
 ✔ Authenticate success
 ✔ Authenticate failure
 ✔ Create license
 ✔ Get license not found
 ✔ Set token

SimpleLicense\Vendor\Tests\Unit\Resources\LicenseTest
 ✔ From array
 ✔ To array
 ✔ Factory active
 ✔ Factory expired

SimpleLicense\Vendor\Tests\Integration\ClientIntegrationTest
 ✔ Complete license lifecycle
 ✔ Authentication token expiration
 ✔ License not found error handling
 ✔ Validation error handling
 ✔ List licenses with filters
```

