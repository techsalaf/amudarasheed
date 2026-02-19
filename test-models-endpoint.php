<?php
/**
 * Quick test script to diagnose the models endpoint
 * Place this in the project root and access it via: http://localhost/dev/test-models-endpoint.php
 */

require __DIR__ . '/bootstrap/app.php';

use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Models\AiApiKey;
use Botble\AiAssistant\Services\ModelFetchers\ModelFetcherFactory;
use Botble\AiAssistant\Services\ModelCacheService;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

echo "<h1>AI Models Endpoint Diagnostic</h1>";
echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; font-family: monospace;'>";

try {
    echo "Step 1: Checking AI Providers in Database\n";
    echo str_repeat("=", 50) . "\n";
    
    $providers = AiProvider::all();
    echo "Total providers: " . count($providers) . "\n\n";
    
    foreach ($providers as $provider) {
        echo "- ID: {$provider->id}, Name: {$provider->name}, Display: {$provider->display_name}\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    echo "Step 2: Checking API Keys\n";
    echo str_repeat("=", 50) . "\n";
    
    $keys = AiApiKey::all();
    echo "Total API keys: " . count($keys) . "\n\n";
    
    foreach ($keys as $key) {
        $provider = $key->provider;
        echo "- Provider: {$provider->name}, Active: " . ($key->is_active ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    echo "Step 3: Testing Model Fetcher Factory\n";
    echo str_repeat("=", 50) . "\n";
    
    ModelFetcherFactory::initialize();
    echo "Factory initialized\n\n";
    
    foreach ($providers as $provider) {
        echo "Provider: {$provider->name}\n";
        $fetcher = ModelFetcherFactory::make(strtolower($provider->name));
        
        if ($fetcher) {
            echo "  ✓ Fetcher found: " . get_class($fetcher) . "\n";
        } else {
            echo "  ✗ No fetcher found\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    echo "Step 4: Testing Model Fetching (if API keys exist)\n";
    echo str_repeat("=", 50) . "\n";
    
    $cacheService = new ModelCacheService();
    
    foreach ($providers->take(1) as $provider) {
        echo "Testing provider: {$provider->display_name}\n";
        
        $apiKey = AiApiKey::query()
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->first();
        
        if (!$apiKey) {
            echo "  ⚠ No active API key found for this provider\n";
            continue;
        }
        
        try {
            $decryptedKey = $apiKey->getDecryptedKey();
            echo "  ✓ API key decrypted successfully\n";
            
            $models = $cacheService->getModels($provider, $decryptedKey, true);
            echo "  ✓ Models fetched: " . count($models) . " models\n";
            
            if (count($models) > 0) {
                echo "  First 3 models:\n";
                foreach (array_slice($models, 0, 3) as $model) {
                    echo "    - {$model}\n";
                }
            }
        } catch (\Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Diagnostic complete!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
?>
