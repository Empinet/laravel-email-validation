<?php

namespace Empinet\EasyEmailApi\Rules;

use Empinet\EasyEmailApi\Services\EmailValidationService;
use Illuminate\Contracts\Validation\Rule;

class EasyEmailApi implements Rule
{
    private string $message = '';

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function __construct(private array $overrides = []) {}

    public function passes($attribute, $value): bool
    {
        $service = app(EmailValidationService::class);
        $result = $service->validate((string) $value, $this->overrides);
        $this->message = $result->message;

        return $result->passes;
    }

    public function message(): string
    {
        if ($this->message === '') {
            return __('The :attribute must be a valid email address.');
        }

        return $this->message;
    }
}
