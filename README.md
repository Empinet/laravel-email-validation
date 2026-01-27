# EasyEmailAPI Laravel Validation Rule

[![CI](https://github.com/Empinet/laravel-email-validation/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/Empinet/laravel-email-validation/actions/workflows/ci.yml)
[![Coverage](https://github.com/Empinet/laravel-email-validation/actions/workflows/coverage.yml/badge.svg?branch=master)](https://github.com/Empinet/laravel-email-validation/actions/workflows/coverage.yml)
[![Release](https://github.com/Empinet/laravel-email-validation/actions/workflows/release.yml/badge.svg?branch=master)](https://github.com/Empinet/laravel-email-validation/actions/workflows/release.yml)

Strict email validation for Laravel using EasyEmailAPI.

This package provides a Laravel validation rule that helps detect invalid, disposable, role-based, and risky email addresses before they enter your system. It is designed for sign-up forms, user registration flows, and any place where email quality and deliverability matter.

## Installation

```bash
composer require empinet/laravel-email-validation
```

Publish the config file:

```bash
php artisan vendor:publish --tag=easyemailapi-config
```

## Getting an API token

To use this package, you need an API token from EasyEmailAPI.
You can create a free account and get your token at https://easyemailapi.com

EasyEmailAPI offers a free tier, which is usually enough for testing, small projects, and personal applications.

Set your token in `.env`:

```
EASYEMAILAPI_TOKEN=your-token
```

## Usage

```php
use Empinet\EasyEmailApi\Rules\EasyEmailApi;

$request->validate([
    'email' => ['required', 'email', new EasyEmailApi()],
]);
```

Override defaults per rule instance:

```php
new EasyEmailApi([
    'disallow_free' => true,
    'disallow_role' => true,
    'min_score' => 50,
])
```

## Configuration

The configuration file is located at `config/easyemailapi.php`. It controls authentication,
validation behavior, caching, and fallback handling.

Key settings:

- `token`: API token (from `EASYEMAILAPI_TOKEN`).
- `auth_mode`: `bearer` (default) or `query`.
- `timeout`: Request timeout in seconds.
- `retries`: Number of retries when a request fails.
- `cache.enabled`: Enable response caching.
- `cache.store`: Cache store to use (optional).
- `cache.ttl`: Cache TTL in seconds (default 24 hours).
- `validation.*`: Defaults for response evaluation.
- `fallback.behavior`: `basic_email` (default), `pass`, `fail`, or `exception`.
- `fallback.log`: Enable logging when fallback triggers (default `true`).
- `fallback.log_level`: Log severity for fallback (default `error`).
- `messages.*`: Customize the validation error text returned to users.

## Response Evaluation Defaults

The default behavior matches the recommended EasyEmailAPI settings:

- `require_mx` = true
- `disallow_disposable` = true
- `disallow_free` = false
- `disallow_role` = false
- `require_inbox_exists` = false
- `min_score` = 0

These defaults provide strong protection against disposable and invalid email addresses
without being overly aggressive.

## Caching

Validation responses are cached per email address and rule options. This prevents repeat
calls to the EasyEmailAPI service for the same email and improves performance for
high-traffic forms and registration endpoints.

## Fallback Behavior

When the EasyEmailAPI service is unavailable, the rule will apply the configured fallback strategy:

- `basic_email`: fall back to Laravel's built-in `email` rule.
- `pass`: allow validation to pass.
- `fail`: fail validation.
- `exception`: throw `EasyEmailApiException`.

This allows you to balance strict validation against availability requirements.

## Testing

```bash
composer install
vendor/bin/phpunit
```

# About EasyEmailAPI

This package is powered by [EasyEmailAPI](https://easyemailapi.com)￼, a real-time email validation service built to help developers prevent fake sign-ups, disposable email abuse, and invalid addresses before they reach the database. EasyEmailAPI goes beyond basic syntax checks by validating MX records, detecting temporary and disposable email providers, and assessing overall email risk, making it ideal for modern applications that care about data quality, deliverability, and protecting registration flows from spam and automated abuse.
