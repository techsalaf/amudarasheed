<?php

namespace Botble\AiAssistant\Commands;

use Botble\AiAssistant\Models\AiApiKey;
use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Services\ModelCacheService;
use Illuminate\Console\Command;

class RefreshAiModelsCommand extends Command
{
    protected $signature = 'ai:refresh-models {--provider= : Specific provider to refresh}';
    protected $description = 'Fetch and cache the latest AI models from all configured providers';

    public function handle(): int
    {
        $this->info('Fetching AI models from providers...');
        
        $cacheService = new ModelCacheService();
        
        if ($providerId = $this->option('provider')) {
            // Refresh specific provider
            $provider = AiProvider::findOrFail($providerId);
            $this->refreshProvider($provider, $cacheService);
        } else {
            // Refresh all active providers
            $providers = AiProvider::where('is_active', true)->get();
            foreach ($providers as $provider) {
                $this->refreshProvider($provider, $cacheService);
            }
        }

        $this->info('✓ AI models refreshed successfully');
        return 0;
    }

    private function refreshProvider(AiProvider $provider, ModelCacheService $cacheService): void
    {
        // Get an active API key for this provider
        $apiKey = AiApiKey::query()
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            $this->warn("  ⚠ {$provider->display_name}: No active API key found");
            return;
        }

        try {
            $decryptedKey = $apiKey->getDecryptedKey();
            
            // Force refresh (clear cache and fetch fresh)
            $cacheService->clearCache($provider->id);
            $models = $cacheService->getModels($provider, $decryptedKey, forceRefresh: true);

            if (empty($models)) {
                $this->warn("  ⚠ {$provider->display_name}: No models returned from API");
                return;
            }

            $this->info("  ✓ {$provider->display_name}: {$this->plural(count($models), 'model')} loaded");
        } catch (\Exception $e) {
            $this->error("  ✗ {$provider->display_name}: {$e->getMessage()}");
        }
    }

    private function plural(int $count, string $word): string
    {
        return "{$count} {$word}" . ($count !== 1 ? 's' : '');
    }
}
