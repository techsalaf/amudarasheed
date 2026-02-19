<?php

namespace Botble\AiAssistant\Services;

use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Services\ModelFetchers\ModelFetcherFactory;
use Illuminate\Support\Facades\Cache;

class ModelCacheService
{
    private const CACHE_PREFIX = 'ai_models_';
    private const CACHE_DURATION = 3600; // 1 hour

    /**
     * Get models for a provider, using cache when available
     */
    public function getModels(AiProvider $provider, string $apiKey, bool $forceRefresh = false): array
    {
        $cacheKey = $this->getCacheKey($provider->id);

        if (!$forceRefresh) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $models = $this->fetchAndCache($provider, $apiKey);
        return $models;
    }

    /**
     * Fetch models from API and cache them
     */
    private function fetchAndCache(AiProvider $provider, string $apiKey): array
    {
        $providerKey = strtolower($provider->name);
        $fetcher = ModelFetcherFactory::make($providerKey);

        if (!$fetcher) {
            return [];
        }

        try {
            $models = $fetcher->fetchModels($apiKey);
            
            // Sort models alphabetically
            sort($models);
            
            // Cache for 1 hour
            Cache::put($this->getCacheKey($provider->id), $models, self::CACHE_DURATION);
            
            return $models;
        } catch (\Exception $e) {
            logger()->warning("Failed to fetch and cache models for {$provider->display_name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear cache for a provider
     */
    public function clearCache(int $providerId): void
    {
        Cache::forget($this->getCacheKey($providerId));
    }

    /**
     * Clear all model caches
     */
    public function clearAllCaches(): void
    {
        // Note: In production, use a more efficient method like tagging
        // For now, this is a simple implementation
        foreach (range(1, 10) as $id) {
            $this->clearCache($id);
        }
    }

    private function getCacheKey(int $providerId): string
    {
        return self::CACHE_PREFIX . $providerId;
    }
}
