<?php

namespace Botble\AiAssistant\Drivers;

use Illuminate\Support\Facades\Http;

class OpenAiDriver implements AiDriverInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $model = 'gpt-4-turbo';

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
                    error: $response->json('error.message') ?? 'OpenAI API error',
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
        $startTime = microtime(true);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(120)
                ->post("{$this->baseUrl}/images/generations", [
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'n' => $options['n'] ?? 1,
                    'size' => $options['size'] ?? '1024x1024',
                    'quality' => $options['quality'] ?? 'standard',
                ]);

            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                return new AiGenerationResult(
                    success: false,
                    error: $response->json('error.message') ?? 'OpenAI Image API error',
                    responseTimeMs: $responseTimeMs,
                    model: 'dall-e-3',
                );
            }

            $data = $response->json();
            $imageUrl = $data['data'][0]['url'] ?? '';

            return new AiGenerationResult(
                success: true,
                content: $imageUrl,
                responseTimeMs: $responseTimeMs,
                model: 'dall-e-3',
            );
        } catch (\Exception $e) {
            return new AiGenerationResult(
                success: false,
                error: 'Error: ' . $e->getMessage(),
                responseTimeMs: (int)((microtime(true) - $startTime) * 1000),
                model: 'dall-e-3',
            );
        }
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function getModels(): array
    {
        return [
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
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
                ->get("{$this->baseUrl}/models");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
