# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Overview

This is the PHP SDK for the Smartling Translation API. It provides a PHP client library for interacting with Smartling's translation management platform, allowing developers to upload files for translation, download translated content, manage translation jobs, and access other Smartling API features.

## Development Commands

### Install Dependencies
```bash
composer install
```

### Run All Tests
Tests require Smartling API credentials as environment variables:
```bash
account_uid=<account_uid> project_id=<project_id> user_id=<user_id> user_key=<user_key> ./vendor/bin/phpunit
```

### Run Specific Test Suites
```bash
# Unit tests only
./vendor/bin/phpunit --testsuite unit

# Functional tests only (requires API credentials)
account_uid=<account_uid> project_id=<project_id> user_id=<user_id> user_key=<user_key> ./vendor/bin/phpunit --testsuite functional
```

### Run Individual Test Files
```bash
./vendor/bin/phpunit tests/unit/SomeTest.php
```

## Architecture

### Core Components

**BaseApiAbstract** (`src/BaseApiAbstract.php`)
- Abstract base class for all API clients
- Handles HTTP communication via Guzzle
- Manages authentication via `AuthApiInterface`
- Implements automatic token refresh on 401 errors
- Provides logging support via PSR-3 `LoggerInterface`
- Standardizes error handling and response processing
- All API classes extend this base

**Authentication Flow**
- `AuthTokenProvider` (`src/AuthApi/`) implements `AuthApiInterface`
- Manages OAuth access tokens and refresh tokens with automatic expiration handling
- Token refresh happens automatically when expired or on 401 responses
- All API clients receive an auth provider instance and use it to authenticate requests

**API Client Pattern**
Each Smartling API endpoint has a dedicated API class:
- `FileApi` - File upload, download, and management
- `JobsApi` - Translation job management
- `BatchApi` / `BatchApiV2` - Batch operations
- `ContextApi` - Visual context and screenshots
- `ProjectApi` - Project information
- `AuditLogApi` - Audit logs
- `ProgressTrackerApi` - Translation progress tracking
- `TranslationRequestsApi` - Translation request management
- `DistributedLockServiceApi` - Distributed locking

**Parameter Objects**
- Located in `*/Params/` directories under each API module
- Implement `ParameterInterface` or extend `BaseParameters`
- Provide type-safe parameter building for API methods
- Example: `UploadFileParameters`, `CreateJobParameters`, `DownloadFileParameters`

### Directory Structure

```
src/
├── BaseApiAbstract.php       # Base class for all API clients
├── AuthApi/                   # Authentication provider
├── File/                      # File API (upload/download)
├── Jobs/                      # Jobs API
├── Batch/                     # Batch operations (v1 and v2)
├── Context/                   # Visual context API
├── Project/                   # Project information API
├── AuditLog/                  # Audit logging API
├── ProgressTracker/           # Progress tracking API
├── TranslationRequests/       # Translation requests API
├── DistributedLockService/    # Distributed lock service
├── Parameters/                # Base parameter classes
└── Exceptions/                # Custom exceptions

tests/
├── unit/                      # Unit tests (no API calls)
└── functional/                # Functional tests (require API credentials)

examples/                      # Usage examples for each API
```

### API Client Instantiation Pattern

All API clients follow this factory pattern:
```php
$authProvider = AuthTokenProvider::create($userIdentifier, $userSecretKey);
$api = SomeApi::create($authProvider, $projectId, $logger);
```

Each API class:
1. Defines its own `ENDPOINT_URL` constant
2. Provides a static `create()` factory method
3. Initializes an HTTP client via `BaseApiAbstract::initializeHttpClient()`
4. Sets the auth provider via `setAuth()`

### Error Handling

- `SmartlingApiException` is thrown for all API errors
- Errors contain structured error information from the API response
- 401 errors trigger automatic token refresh and retry
- Sensitive data (tokens, credentials) is redacted from logs

### Logging

- All API clients accept an optional PSR-3 `LoggerInterface`
- Requests and responses are logged for debugging
- Sensitive data is automatically redacted in logs

## PHP Version Support

- PHP 7.3+ and PHP 8.x
- Uses PSR-4 autoloading
- Requires Guzzle 6.x or 7.x for HTTP client
- Requires PSR Log 1.x or 3.x for logging

## Testing Strategy

- **Unit tests** (`tests/unit/`) - Test logic without making actual API calls
- **Functional tests** (`tests/functional/`) - Integration tests that call the real Smartling API
- Functional tests require valid Smartling credentials passed as environment variables
- Test resources are stored in `tests/resources/`

## Examples

The `examples/` directory contains working examples for each API:
- Run with: `php file-example.php --project-id=XXX --user-id=XXX --secret-key=XXX`
- Each example demonstrates common operations for that API
- Examples are self-contained and include inline documentation
