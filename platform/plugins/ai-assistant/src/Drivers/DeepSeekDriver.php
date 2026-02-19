<?php

namespace Botble\AiAssistant\Drivers;

use Illuminate\Support\Facades\Http;

class DeepSeekDriver implements AiDriverInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.deepseek.com/v1';
    protected string $model = 'deepseek-chat';

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

            $response = Http::withToken($this->apiKey)
                ->timeout(120)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                ]);

            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                return new AiGenerationResult(
                    success: false,
                    error: $response->json('error.message') ?? 'DeepSeek API error',
                    responseTimeMs: $responseTimeMs,
                    model: $this->model,
                );
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';

            return new AiGenerationResult(
                success: true,
                content: $content,
                inputTokens: $data['usage']['prompt_tokens'] ?? null,
                outputTokens: $data['usage']['completion_tokens'] ?? null,
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
            error: 'Image generation not supported by DeepSeek driver',
            model: $this->model,
        );
    }

    public function getName(): string
    {
        return 'deepseek';
    }

    public function getModels(): array
    {
        return [
            'deepseek-chat' => 'DeepSeek Chat',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function validateApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post('https://api.deepseek.com/v1/chat/completions', [
                    'model' => 'deepseek-chat',
                    'messages' => [['role' => 'user', 'content' => 'test']],
                    'max_tokens' => 10,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
