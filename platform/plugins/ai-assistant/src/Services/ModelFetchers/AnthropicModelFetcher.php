<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

use Illuminate\Support\Facades\Http;

class AnthropicModelFetcher implements ModelFetcherInterface
{
    public function getProviderKey(): string
    {
        return 'claude';
    }

    public function fetchModels(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->get('https://api.anthropic.com/v1/models');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch models from Anthropic: ' . $response->status());
            }

            $models = $response->json('data', []);
            
            $modelIds = collect($models)
                ->pluck('id')
                ->values()
                ->toArray();

            return !empty($modelIds) ? $modelIds : [];
        } catch (\Exception $e) {
            logger()->warning("Anthropic model fetch failed: " . $e->getMessage());
            return [];
        }
    }
}
