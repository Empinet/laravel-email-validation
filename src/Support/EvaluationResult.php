<?php

namespace Empinet\EasyEmailApi\Support;

class EvaluationResult
{
    public function __construct(
        public bool $passes,
        public string $message,
        /** @var array<string, mixed> */
        public array $response = [],
        public bool $fromCache = false
    ) {}
}
