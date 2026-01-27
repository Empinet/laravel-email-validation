<?php

namespace Empinet\EasyEmailApi\Services;

use Empinet\EasyEmailApi\Clients\EasyEmailApiClient;
use Empinet\EasyEmailApi\Exceptions\EasyEmailApiException;
use Empinet\EasyEmailApi\Support\EvaluationResult;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EmailValidationService
{
    public function __construct(
        private EasyEmailApiClient $client,
        private CacheManager $cacheManager,
        /** @var array<string, mixed> */
        private array $config
    ) {}

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function validate(string $email, array $overrides = []): EvaluationResult
    {
        $options = $this->options($overrides);
        $cachedResult = $this->cachedResult($email, $options);
        if ($cachedResult) {
            return $cachedResult;
        }

        try {
            $response = $this->client->verify($email);
        } catch (Throwable $exception) {
            return $this->handleFallback($email, $exception);
        }

        if (! is_array($response) || ! array_key_exists('valid', $response)) {
            return $this->handleFallback($email, null, 'invalid_response');
        }

        $result = $this->evaluateResponse($response, $options);

        $this->storeCache($email, $options, $result, $response);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $options
     */
    private function evaluateResponse(array $response, array $options): EvaluationResult
    {
        if (empty($response['valid'])) {
            return $this->fail('invalid_format', 'Invalid email.');
        }

        if (($options['require_mx'] ?? false) && empty($response['valid_mx'])) {
            return $this->fail('invalid_mx', 'Invalid MX.');
        }

        if (($options['disallow_disposable'] ?? false) && ! empty($response['disposable'])) {
            return $this->fail('disposable', 'Disposable email.');
        }

        if (($options['disallow_free'] ?? false) && ! empty($response['free_email'])) {
            return $this->fail('free_email', 'Free email.');
        }

        if (($options['disallow_role'] ?? false) && ! empty($response['role'])) {
            return $this->fail('role_email', 'Role email.');
        }

        if (($options['require_inbox_exists'] ?? false) && empty($response['inbox_exists'])) {
            return $this->fail('inbox_missing', 'Inbox missing.');
        }

        $minScore = (int) ($options['min_score'] ?? 0);
        if ($minScore > 0 && (int) ($response['score'] ?? 0) < $minScore) {
            return $this->fail('low_score', 'Low score.');
        }

        return new EvaluationResult(true, '', $response);
    }

    private function handleFallback(string $email, ?Throwable $exception = null, string $messageKey = 'api_unavailable'): EvaluationResult
    {
        $behavior = Arr::get($this->config, 'fallback.behavior', 'basic_email');
        $this->logFallback($email, $behavior, $exception);

        return match ($behavior) {
            'exception' => throw new EasyEmailApiException($this->message($messageKey, 'Email validation failed.'), 0, $exception),
            'pass' => new EvaluationResult(true, $this->message($messageKey, 'Email validation skipped.')),
            'basic_email' => $this->basicEmailFallback($email),
            default => $this->fail($messageKey, 'Email validation failed.'),
        };
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function cacheKey(string $email, array $options): string
    {
        $normalized = strtolower($email);
        $payload = $normalized.'|'.json_encode($options);

        return 'easyemailapi:'.sha1($payload);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function options(array $overrides): array
    {
        return array_merge($this->config['validation'] ?? [], $overrides);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function cachedResult(string $email, array $options): ?EvaluationResult
    {
        if (! Arr::get($this->config, 'cache.enabled', true)) {
            return null;
        }

        $cached = $this->cache()->get($this->cacheKey($email, $options));
        if (! is_array($cached)) {
            return null;
        }

        return new EvaluationResult(
            $cached['passes'],
            $cached['message'],
            $cached['response'] ?? [],
            true
        );
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $response
     */
    private function storeCache(string $email, array $options, EvaluationResult $result, array $response): void
    {
        if (! Arr::get($this->config, 'cache.enabled', true)) {
            return;
        }

        $this->cache()->put($this->cacheKey($email, $options), [
            'passes' => $result->passes,
            'message' => $result->message,
            'response' => $response,
        ], $this->cacheTtl());
    }

    private function basicEmailFallback(string $email): EvaluationResult
    {
        $validator = Validator::make(['email' => $email], ['email' => 'email']);
        if ($validator->passes()) {
            return new EvaluationResult(true, '');
        }

        return $this->fail('invalid_format', 'Invalid email.');
    }

    private function logFallback(string $email, string $behavior, ?Throwable $exception): void
    {
        if (! Arr::get($this->config, 'fallback.log', false)) {
            return;
        }

        $level = Arr::get($this->config, 'fallback.log_level', 'warning');
        Log::log($level, 'EasyEmailAPI validation fallback triggered.', [
            'email' => $email,
            'behavior' => $behavior,
            'exception' => $exception?->getMessage(),
        ]);
    }

    private function message(string $key, string $default): string
    {
        return $this->config['messages'][$key] ?? $default;
    }

    private function fail(string $messageKey, string $defaultMessage): EvaluationResult
    {
        return new EvaluationResult(false, $this->message($messageKey, $defaultMessage));
    }

    private function cacheTtl(): int
    {
        return (int) Arr::get($this->config, 'cache.ttl', 60 * 60 * 24);
    }

    private function cache(): Repository
    {
        $store = Arr::get($this->config, 'cache.store');

        return $store ? $this->cacheManager->store($store) : $this->cacheManager->store();
    }
}
