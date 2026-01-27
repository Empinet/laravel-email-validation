<?php

namespace Empinet\EasyEmailApi\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class EasyEmailApiClient
{
    private const BASE_URL = 'https://easyemailapi.com';

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private array $config) {}

    /**
     * @throws RequestException
     */
    /**
     * @return array<string, mixed>
     */
    public function verify(string $email): array
    {
        $token = Arr::get($this->config, 'token');
        $authMode = Arr::get($this->config, 'auth_mode', 'bearer');
        $timeout = (int) Arr::get($this->config, 'timeout', 5);
        $retries = (int) Arr::get($this->config, 'retries', 1);

        $request = $this->buildRequest($token, $authMode)
            ->timeout($timeout)
            ->retry($retries);

        $url = $this->buildUrl($email, $authMode, $token);

        return $request->get($url)->throw()->json();
    }

    private function buildRequest(?string $token, string $authMode): PendingRequest
    {
        if ($authMode === 'bearer' && $token) {
            return Http::withToken($token);
        }

        return Http::baseUrl(self::BASE_URL);
    }

    private function buildUrl(string $email, string $authMode, ?string $token): string
    {
        $encodedEmail = rawurlencode($email);
        $path = '/api/verify/'.$encodedEmail;

        if ($authMode === 'query' && $token) {
            return self::BASE_URL.$path.'?token='.rawurlencode($token);
        }

        return self::BASE_URL.$path;
    }
}
