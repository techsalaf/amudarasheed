<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

use Illuminate\Support\Facades\Http;

class GrokModelFetcher implements ModelFetcherInterface
{
    public function getProviderKey(): string
    {
        return 'grok';
    }

    public function fetchModels(string $apiKey): array
    {
        try {
            // Grok models through xAI API
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->get('https://api.x.ai/v1/models');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch models from Grok: ' . $response->status());
            }

            $models = $response->json('data', []);
            
            $modelIds = collect($models)
                ->pluck('id')
                ->values()
                ->toArray();

            return !empty($modelIds) ? $modelIds : [];
        } catch (\Exception $e) {
            logger()->warning("Grok model fetch failed: " . $e->getMessage());
            return [];
        }
    }
}
