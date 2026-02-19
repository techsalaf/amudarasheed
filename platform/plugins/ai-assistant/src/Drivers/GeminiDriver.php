<?php

namespace Botble\AiAssistant\Drivers;

use Illuminate\Support\Facades\Http;

class GeminiDriver implements AiDriverInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    protected string $model = 'gemini-pro';

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
            $systemInstruction = 'You are a helpful assistant for content generation.';
            if ($customInstruction) {
                $systemInstruction .= "\n\n" . $customInstruction;
            }

            $response = Http::timeout(120)
                ->post("{$this->baseUrl}/{$this->model}:generateContent", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $prompt]],
                        ],
                    ],
                    'systemInstruction' => ['parts' => [['text' => $systemInstruction]]],
                    'generationConfig' => [
                        'temperature' => $options['temperature'] ?? 0.7,
                        'maxOutputTokens' => $options['max_tokens'] ?? 1000,
                    ],
                ], [
                    'key' => $this->apiKey,
                ]);

            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                return new AiGenerationResult(
                    success: false,
                    error: $response->json('error.message') ?? 'Gemini API error',
                    responseTimeMs: $responseTimeMs,
                    model: $this->model,
                );
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $usageData = $data['usageMetadata'] ?? [];

            return new AiGenerationResult(
                success: true,
                content: $content,
                inputTokens: $usageData['promptTokenCount'] ?? null,
                outputTokens: $usageData['candidatesTokenCount'] ?? null,
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
        // Gemini doesn't have a native image generation API; could use Imagen if available
        return new AiGenerationResult(
            success: false,
            error: 'Image generation not supported by Gemini driver',
            model: $this->model,
        );
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public function getModels(): array
    {
        return [
            'gemini-pro' => 'Gemini Pro',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function validateApiKey(string $apiKey): bool
    {
        try {
            $response = Http::timeout(10)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent", [
                    'contents' => [
                        [
                            'parts' => [['text' => 'test']],
                        ],
                    ],
                ], [
                    'key' => $apiKey,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
