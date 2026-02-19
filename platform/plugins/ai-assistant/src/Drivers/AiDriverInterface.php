<?php

namespace Botble\AiAssistant\Drivers;

interface AiDriverInterface
{
    /**
     * Generate text content
     */
    public function generateText(
        string $prompt,
        array $options = [],
        ?string $customInstruction = null
    ): AiGenerationResult;

    /**
     * Generate image from prompt
     */
    public function generateImage(
        string $prompt,
        array $options = [],
    ): AiGenerationResult;

    /**
     * Get the driver name
     */
    public function getName(): string;

    /**
     * Get available models
     */
    public function getModels(): array;

    /**
     * Check if driver is configured
     */
    public function isConfigured(): bool;

    /**
     * Validate the API key
     */
    public function validateApiKey(string $apiKey): bool;
}
