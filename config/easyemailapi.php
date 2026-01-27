<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | These options control how the API response is evaluated. They are the
    | most commonly adjusted settings for tailoring validation behavior.
    |
    */
    'validation' => [
        'require_mx' => true,
        'disallow_disposable' => true,
        'disallow_free' => false,
        'disallow_role' => false,
        'require_inbox_exists' => false,
        'min_score' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | Customize the validation error text returned to users. Defaults are
    | intentionally generic to avoid leaking verification details.
    |
    */
    'messages' => [
        'invalid_format' => 'The :attribute must be a valid email address.',
        'invalid_mx' => 'The :attribute must be a valid email address.',
        'disposable' => 'The :attribute must be a valid email address.',
        'free_email' => 'The :attribute must be a valid email address.',
        'role_email' => 'The :attribute must be a valid email address.',
        'inbox_missing' => 'The :attribute must be a valid email address.',
        'low_score' => 'The :attribute must be a valid email address.',
        'api_unavailable' => 'The :attribute must be a valid email address.',
        'invalid_response' => 'The :attribute must be a valid email address.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Provide your EasyEmailAPI token and choose the auth mode.
    |
    */
    'token' => env('EASYEMAILAPI_TOKEN'),

    'auth_mode' => env('EASYEMAILAPI_AUTH_MODE', 'bearer'),

    /*
    |--------------------------------------------------------------------------
    | Request Behavior
    |--------------------------------------------------------------------------
    |
    | Configure timeouts and retry attempts for API requests.
    |
    */
    'timeout' => env('EASYEMAILAPI_TIMEOUT', 5),

    'retries' => env('EASYEMAILAPI_RETRIES', 1),

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Cache API responses by email and options to reduce repeat calls.
    |
    */
    'cache' => [
        'enabled' => env('EASYEMAILAPI_CACHE_ENABLED', true),
        'store' => env('EASYEMAILAPI_CACHE_STORE'),
        'ttl' => env('EASYEMAILAPI_CACHE_TTL', 60 * 60 * 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Behavior
    |--------------------------------------------------------------------------
    |
    | Control how validation behaves when the API is unavailable.
    |
    */
    'fallback' => [
        'behavior' => env('EASYEMAILAPI_FALLBACK', 'basic_email'),
        'log' => env('EASYEMAILAPI_FALLBACK_LOG', true),
        'log_level' => env('EASYEMAILAPI_FALLBACK_LOG_LEVEL', 'error'),
    ],
];
