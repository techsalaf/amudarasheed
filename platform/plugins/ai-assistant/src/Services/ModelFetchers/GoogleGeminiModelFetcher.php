<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

use Illuminate\Support\Facades\Http;

class GoogleGeminiModelFetcher implements ModelFetcherInterface
{
    public function getProviderKey(): string
    {
        return 'gemini';
    }

    public function fetchModels(string $apiKey): array
    {
        try {
            // Google's GenAI API uses the API key in the query string
            $response = Http::get('https://generativelanguage.googleapis.com/v1beta/models', [
                'key' => $apiKey,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch models from Google Gemini: ' . $response->status());
            }

            $models = $response->json('models', []);
            
            $modelIds = collect($models)
                ->map(function ($model) {
                    // Model name is like "models/gemini-pro", extract the ID
                    $name = $model['name'] ?? '';
                    return str_replace('models/', '', $name);
                })
                ->filter() // Remove null/empty values
                ->values()
                ->toArray();

            return !empty($modelIds) ? $modelIds : [];
        } catch (\Exception $e) {
            logger()->warning("Google Gemini model fetch failed: " . $e->getMessage());
            return [];
        }
    }
}
