<?php

namespace Botble\AiAssistant\Services;

use Botble\AiAssistant\Drivers\AiDriverInterface;
use Botble\AiAssistant\Drivers\AiGenerationResult;
use Botble\AiAssistant\Drivers\ClaudeDriver;
use Botble\AiAssistant\Drivers\DeepSeekDriver;
use Botble\AiAssistant\Drivers\GeminiDriver;
use Botble\AiAssistant\Drivers\GrokDriver;
use Botble\AiAssistant\Drivers\OpenAiDriver;
use Botble\AiAssistant\Drivers\OpenRouterDriver;
use Botble\AiAssistant\Models\AiApiKey;
use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Models\AiSetting;
use Botble\AiAssistant\Models\AiUsageLog;
use Illuminate\Support\Collection;

class AiGenerationService
{
    protected Collection $drivers;

    public function __construct()
    {
        $this->drivers = new Collection();
        $this->registerDefaultDrivers();
    }

    protected function registerDefaultDrivers(): void
    {
        // Drivers will be instantiated on-demand with API keys
    }

    /**
     * Generate text with automatic fallback
     */
    public function generateText(
        string $prompt,
        array $options = [],
        ?string $customInstruction = null
    ): AiGenerationResult {
        $apiKeys = $this->getAvailableApiKeysInOrder();

        foreach ($apiKeys as $apiKey) {
            $driver = $this->getDriver($apiKey);
            if (!$driver) {
                continue;
            }

            $result = $driver->generateText($prompt, $options, $customInstruction);

            if ($result->success) {
                // Log successful usage
                $this->logUsage($apiKey, $result, 'text_generation', $prompt);
                
                // Update token count
                if ($result->getTotalTokens() > 0) {
                    $apiKey->increment('monthly_tokens_used', $result->getTotalTokens());
                }

                return $result;
            } else {
                // Log failed attempt
                $this->logUsage($apiKey, $result, 'text_generation', $prompt, true);
            }
        }

        // All keys exhausted or failed
        return new AiGenerationResult(
            success: false,
            error: 'No available AI providers or all have insufficient quota',
        );
    }

    /**
     * Generate image with automatic fallback
     */
    public function generateImage(
        string $prompt,
        array $options = []
    ): AiGenerationResult {
        $apiKeys = $this->getAvailableApiKeysInOrder();

        foreach ($apiKeys as $apiKey) {
            $driver = $this->getDriver($apiKey);
            if (!$driver) {
                continue;
            }

            $result = $driver->generateImage($prompt, $options);

            if ($result->success) {
                $this->logUsage($apiKey, $result, 'image_generation', $prompt);
                
                if ($result->getTotalTokens() > 0) {
                    $apiKey->increment('monthly_tokens_used', $result->getTotalTokens());
                }

                return $result;
            } else {
                $this->logUsage($apiKey, $result, 'image_generation', $prompt, true);
            }
        }

        return new AiGenerationResult(
            success: false,
            error: 'No available AI providers for image generation',
        );
    }

    /**
     * Get all active API keys ordered by priority and availability
     */
    protected function getAvailableApiKeysInOrder(): Collection
    {
        return AiApiKey::with('provider')
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->filter(function (AiApiKey $key) {
                return $key->hasTokensAvailable();
            });
    }

    /**
     * Get driver instance for an API key
     */
    protected function getDriver(AiApiKey $apiKey): ?AiDriverInterface
    {
        $providerName = $apiKey->provider->name;
        $decryptedKey = $apiKey->getDecryptedKey();
        $model = $apiKey->model;

        return match ($providerName) {
            'openai' => new OpenAiDriver($decryptedKey, $model),
            'gemini' => new GeminiDriver($decryptedKey, $model),
            'claude' => new ClaudeDriver($decryptedKey, $model),
            'deepseek' => new DeepSeekDriver($decryptedKey, $model),
            'openrouter' => new OpenRouterDriver($decryptedKey, $model),
            'grok' => new GrokDriver($decryptedKey, $model),
            default => null,
        };
    }

    /**
     * Log usage to database
     */
    protected function logUsage(
        AiApiKey $apiKey,
        AiGenerationResult $result,
        string $requestType,
        string $prompt,
        bool $isFailed = false
    ): void {
        AiUsageLog::create([
            'api_key_id' => $apiKey->id,
            'model' => $result->model,
            'request_type' => $requestType,
            'prompt' => $prompt,
            'response' => $isFailed ? null : $result->content,
            'input_tokens' => $result->inputTokens,
            'output_tokens' => $result->outputTokens,
            'status' => $isFailed ? 'failed' : 'success',
            'error_message' => $isFailed ? $result->error : null,
            'cost' => $result->cost,
            'response_time_ms' => $result->responseTimeMs,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Test API key validity
     */
    public function validateApiKey(AiApiKey $apiKey): bool
    {
        $driver = $this->getDriver($apiKey);
        if (!$driver) {
            return false;
        }

        return $driver->validateApiKey($apiKey->getDecryptedKey());
    }

    /**
     * Get default model/provider setting
     */
    public function getDefaultModel(): ?string
    {
        return AiSetting::get('default_model');
    }

    /**
     * Get settings
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return AiSetting::get($key, $default);
    }

    /**
     * Update settings
     */
    public function setSetting(string $key, mixed $value, string $type = 'string'): void
    {
        AiSetting::set($key, $value, $type);
    }

    /**
     * Check if PII protection is enabled
     */
    public function isPiiProtectionEnabled(): bool
    {
        return AiSetting::get('enable_pii_protection', true) === true || 
               AiSetting::get('enable_pii_protection', true) === 'true';
    }
}
