<?php

namespace Empinet\EasyEmailApi\Tests;

use Empinet\EasyEmailApi\Rules\EasyEmailApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RuleTest extends TestCase
{
    public function test_rule_passes_for_valid_response(): void
    {
        Http::fake([
            '*' => Http::response($this->validResponse()),
        ]);

        $validator = Validator::make([
            'email' => 'tcook@apple.com',
        ], [
            'email' => [new EasyEmailApi],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_rule_fails_for_disposable_email(): void
    {
        Http::fake([
            '*' => Http::response(array_merge($this->validResponse(), ['disposable' => true])),
        ]);

        $validator = Validator::make([
            'email' => 'temp@trashmail.com',
        ], [
            'email' => [new EasyEmailApi],
        ]);

        $this->assertFalse($validator->passes());
    }

    public function test_rule_fails_for_invalid_mx(): void
    {
        Http::fake([
            '*' => Http::response(array_merge($this->validResponse(), ['valid_mx' => false])),
        ]);

        $validator = Validator::make([
            'email' => 'tcook@apple.com',
        ], [
            'email' => [new EasyEmailApi],
        ]);

        $this->assertFalse($validator->passes());
    }

    public function test_cache_prevents_repeated_requests(): void
    {
        Http::fake([
            '*' => Http::response($this->validResponse()),
        ]);

        $validator = Validator::make([
            'email' => 'tcook@apple.com',
        ], [
            'email' => [new EasyEmailApi],
        ]);

        $this->assertTrue($validator->passes());
        $this->assertTrue($validator->passes());

        Http::assertSentCount(1);
    }

    public function test_fallback_basic_email_when_api_fails(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $validator = Validator::make([
            'email' => 'valid@example.com',
        ], [
            'email' => [new EasyEmailApi],
        ]);

        $this->assertTrue($validator->passes());

        $invalid = Validator::make([
            'email' => 'not-an-email',
        ], [
            'email' => [new EasyEmailApi],
        ]);

        $this->assertFalse($invalid->passes());
    }

    public function test_query_auth_mode_adds_token_to_url(): void
    {
        $this->app['config']->set('easyemailapi.auth_mode', 'query');

        Http::fake(function ($request) {
            $this->assertStringContainsString('token=test-token', $request->url());
            $this->assertFalse($request->hasHeader('Authorization'));

            return Http::response($this->validResponse());
        });

        $validator = Validator::make([
            'email' => 'tcook@apple.com',
        ], [
            'email' => [new EasyEmailApi],
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_override_rules_per_instance(): void
    {
        $response = array_merge($this->validResponse(), ['free_email' => true]);

        Http::fake([
            '*' => Http::response($response),
        ]);

        $validator = Validator::make([
            'email' => 'someone@gmail.com',
        ], [
            'email' => [new EasyEmailApi(['disallow_free' => true])],
        ]);

        $this->assertFalse($validator->passes());
    }

    /**
     * @return array<string, mixed>
     */
    private function validResponse(): array
    {
        return [
            'email' => 'tcook@apple.com',
            'valid' => true,
            'user' => 'tcook',
            'domain' => 'apple.com',
            'role' => false,
            'disposable' => false,
            'free_email' => false,
            'valid_mx' => true,
            'mx' => '',
            'sub' => false,
            'score' => 60,
            'max_score' => 60,
            'inbox_exists' => false,
            'inbox_check_enabled' => false,
        ];
    }
}
