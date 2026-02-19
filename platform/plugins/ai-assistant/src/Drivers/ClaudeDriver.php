<?php

namespace Botble\AiAssistant\Drivers;

use Illuminate\Support\Facades\Http;

class ClaudeDriver implements AiDriverInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.anthropic.com/v1';
    protected string $model = 'claude-3-opus-20240229';

    public function __construct(string $apiKey, ?string $model = null)
    {
        $this->apiKey = $apiKey;
        if ($model) {
            $this->model = $model;
        }
    }

    public function generateText(
        string $prompt,
        array $options = [],
        ?string $customInstruction = null
    ): AiGenerationResult {
        $startTime = microtime(true);

        try {
            $systemPrompt = 'You are a helpful assistant for content generation.';
            if ($customInstruction) {
                $systemPrompt .= "\n\n" . $customInstruction;
            }

            $response = Http::withHeaders([
                'anthropic-version' => '2023-06-01',
                'x-api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ])->timeout(120)
                ->post("{$this->baseUrl}/messages", [
                    'model' => $this->model,
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                return new AiGenerationResult(
                    success: false,
                    error: $response->json('error.message') ?? 'Claude API error',
                    responseTimeMs: $responseTimeMs,
                    model: $this->model,
                );
            }

            $data = $response->json();
            $content = $data['content'][0]['text'] ?? '';
            $usageData = $data['usage'] ?? [];

            return new AiGenerationResult(
                success: true,
                content: $content,
                inputTokens: $usageData['input_tokens'] ?? null,
                outputTokens: $usageData['output_tokens'] ?? null,
                responseTimeMs: $responseTimeMs,
                model: $this->model,
            );
        } catch (\Exception $e) {
            return new AiGenerationResult(
                success: false,
                error: 'Error: ' . $e->getMessage(),
                responseTimeMs: (int)((microtime(true) - $startTime) * 1000),
                model: $this->model,
            );
        }
    }

    public function generateImage(
        string $prompt,
        array $options = [],
    ): AiGenerationResult {
        return new AiGenerationResult(
            success: false,
            error: 'Image generation not supported by Claude driver',
            model: $this->model,
        );
    }

    public function getName(): string
    {
        return 'claude';
    }

    public function getModels(): array
    {
        return [
            'claude-3-opus-20240229' => 'Claude 3 Opus',
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
            'claude-3-haiku-20240307' => 'Claude 3 Haiku',
            'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function validateApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withHeaders([
                'anthropic-version' => '2023-06-01',
                'x-api-key' => $apiKey,
                'content-type' => 'application/json',
            ])->timeout(10)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 10,
                    'messages' => [['role' => 'user', 'content' => 'test']],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
