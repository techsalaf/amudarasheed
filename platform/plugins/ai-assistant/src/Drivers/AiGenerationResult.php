<?php

namespace Botble\AiAssistant\Drivers;

use DateTime;

class AiGenerationResult
{
    public function __construct(
        public bool $success,
        public string $content = '',
        public ?int $inputTokens = null,
        public ?int $outputTokens = null,
        public ?string $error = null,
        public ?float $cost = null,
        public ?int $responseTimeMs = null,
        public string $model = '',
    ) {}

    public function getTotalTokens(): int
    {
        return ($this->inputTokens ?? 0) + ($this->outputTokens ?? 0);
    }
}
