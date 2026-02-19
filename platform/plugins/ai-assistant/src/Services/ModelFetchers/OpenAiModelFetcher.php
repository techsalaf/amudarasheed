<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

use Illuminate\Support\Facades\Http;

class OpenAiModelFetcher implements ModelFetcherInterface
{
    public function getProviderKey(): string
    {
        return 'openai';
    }

    public function fetchModels(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->get('https://api.openai.com/v1/models');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch models from OpenAI: ' . $response->status());
            }

            $models = $response->json('data', []);
            
            // Filter for chat/text models (exclude embedding, image models, etc.)
            $textModels = collect($models)
                ->filter(function ($model) {
                    $id = $model['id'] ?? '';
                    // Include GPT models
                    return str_starts_with($id, 'gpt-');
                })
                ->pluck('id')
                ->values()
                ->toArray();

            // If no models found, return empty array (don't throw, just return defaults)
            return !empty($textModels) ? $textModels : [];
        } catch (\Exception $e) {
            // Return empty array on error, let controller handle fallback to defaults
            logger()->warning("OpenAI model fetch failed: " . $e->getMessage());
            return [];
        }
    }
}
