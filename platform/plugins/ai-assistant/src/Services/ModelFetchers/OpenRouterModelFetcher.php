<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

use Illuminate\Support\Facades\Http;

class OpenRouterModelFetcher implements ModelFetcherInterface
{
    public function getProviderKey(): string
    {
        return 'openrouter';
    }

    public function fetchModels(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->get('https://openrouter.ai/api/v1/models');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch models from OpenRouter: ' . $response->status());
            }

            $models = $response->json('data', []);
            
            $modelIds = collect($models)
                ->pluck('id')
                ->values()
                ->toArray();

            return !empty($modelIds) ? $modelIds : [];
        } catch (\Exception $e) {
            logger()->warning("OpenRouter model fetch failed: " . $e->getMessage());
            return [];
        }
    }
}
