<?php

namespace Botble\AiAssistant\Services\ModelFetchers;

use Illuminate\Support\Facades\Http;

class DeepSeekModelFetcher implements ModelFetcherInterface
{
    public function getProviderKey(): string
    {
        return 'deepseek';
    }

    public function fetchModels(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->get('https://api.deepseek.com/v1/models');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch models from DeepSeek: ' . $response->status());
            }

            $models = $response->json('data', []);
            
            $modelIds = collect($models)
                ->pluck('id')
                ->values()
                ->toArray();

            return !empty($modelIds) ? $modelIds : [];
        } catch (\Exception $e) {
            logger()->warning("DeepSeek model fetch failed: " . $e->getMessage());
            return [];
        }
    }
}
