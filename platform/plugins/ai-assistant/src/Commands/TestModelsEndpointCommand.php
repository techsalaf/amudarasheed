<?php

namespace Botble\AiAssistant\Commands;

use Botble\AiAssistant\Models\AiApiKey;
use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Services\ModelCacheService;
use Botble\AiAssistant\Services\ModelFetchers\ModelFetcherFactory;
use Illuminate\Console\Command;

class TestModelsEndpointCommand extends Command
{
    protected $signature = 'ai:test-models {provider_id?}';
    protected $description = 'Test the models endpoint and diagnose issues';

    public function handle(): int
    {
        $this->info('🔍 AI Models Endpoint Diagnostic Test');
        $this->line(str_repeat('=', 50));

        $this->line("\n📦 Step 1: Checking AI Providers in Database");
        $this->line(str_repeat('=', 50));

        $providers = AiProvider::all();
        $this->info("Total providers found: " . count($providers));

        foreach ($providers as $provider) {
            $this->line("  • ID: {$provider->id}, Name: {$provider->name}, Display: {$provider->display_name}");
        }

        $this->line("\n🔑 Step 2: Checking API Keys");
        $this->line(str_repeat('=', 50));

        $keys = AiApiKey::all();
        $this->info("Total API keys found: " . count($keys));

        foreach ($keys as $key) {
            $provider = $key->provider;
            $status = $key->is_active ? '✓ Active' : '✗ Inactive';
            $this->line("  • Provider: {$provider->name} - {$status}");
        }

        $this->line("\n⚙️  Step 3: Testing Model Fetcher Factory");
        $this->line(str_repeat('=', 50));

        ModelFetcherFactory::initialize();
        $this->info("Factory initialized successfully");

        foreach ($providers as $provider) {
            $fetcher = ModelFetcherFactory::make(strtolower($provider->name));
            if ($fetcher) {
                $this->line("  ✓ {$provider->name}: " . class_basename($fetcher));
            } else {
                $this->warn("  ✗ {$provider->name}: No fetcher found");
            }
        }

        $this->line("\n📡 Step 4: Testing Model Fetching");
        $this->line(str_repeat('=', 50));

        $providerId = $this->argument('provider_id');

        if ($providerId) {
            $provider = AiProvider::find((int)$providerId);
            if (!$provider) {
                $this->error("Provider with ID {$providerId} not found");
                return 1;
            }
            $providersToTest = [$provider];
        } else {
            $providersToTest = $providers->take(1)->all();
        }

        $cacheService = new ModelCacheService();

        foreach ($providersToTest as $provider) {
            $this->line("\nTesting: {$provider->display_name} (ID: {$provider->id})");
            $this->line("-" . str_repeat("-", 48));

            $apiKey = AiApiKey::query()
                ->where('provider_id', $provider->id)
                ->where('is_active', true)
                ->first();

            if (!$apiKey) {
                $this->warn("⚠️  No active API key found for this provider");
                continue;
            }

            try {
                $decryptedKey = $apiKey->getDecryptedKey();
                $this->info("✓ API key decrypted successfully");

                // Force refresh to test live API call
                $models = $cacheService->getModels($provider, $decryptedKey, true);
                $this->info("✓ Models fetched: " . count($models) . " models");

                if (count($models) > 0) {
                    $this->line("First 5 models:");
                    foreach (array_slice($models, 0, 5) as $model) {
                        $this->line("  - {$model}");
                    }
                } else {
                    $this->warn("No models returned from API");
                }
            } catch (\Exception $e) {
                $this->error("✗ Error: " . $e->getMessage());
                $this->getOutput()->writeln($e->getTraceAsString());
            }
        }

        $this->line("\n" . str_repeat('=', 50));
        $this->info("✅ Diagnostic complete!");

        return 0;
    }
}
