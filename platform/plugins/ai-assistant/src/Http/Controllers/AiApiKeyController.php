<?php

namespace Botble\AiAssistant\Http\Controllers;

use Botble\AiAssistant\Models\AiApiKey;
use Botble\AiAssistant\Models\AiCustomInstruction;
use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Services\AiGenerationService;
use Botble\AiAssistant\Services\ModelCacheService;
use Botble\AiAssistant\Services\ModelFetchers\ModelFetcherFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiApiKeyController
{
    public function __construct(
        protected AiGenerationService $aiService
    ) {}

    /**
     * Show API keys list
     */
    public function index()
    {
        $apiKeys = AiApiKey::with('provider')
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20);

        return view('plugins/ai-assistant::admin.keys.index', [
            'apiKeys' => $apiKeys,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $providers = AiProvider::where('is_active', true)->get();

        return view('plugins/ai-assistant::admin.keys.create', [
            'providers' => $providers,
        ]);
    }

    /**
     * Store new API key
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_providers,id',
            'label' => 'nullable|string|max:255',
            'key' => 'required|string|min:10',
            'model' => 'nullable|string|max:255',
            'monthly_token_limit' => 'nullable|integer|min:0',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $provider = AiProvider::findOrFail($validated['provider_id']);

        // Create temporary API key object for validation
        $tempKey = new AiApiKey([
            'provider_id' => $provider->id,
            'key_encrypted' => encrypt($validated['key']),
            'model' => $validated['model'] ?? null,
        ]);
        $tempKey->setRelation('provider', $provider);

        // Validate API key with the provider
        if (!$this->aiService->validateApiKey($tempKey)) {
            return back()
                ->withInput()
                ->with('error', 'Invalid API key for ' . $provider->display_name);
        }

        AiApiKey::create([
            'provider_id' => $validated['provider_id'],
            'label' => $validated['label'],
            'key_encrypted' => encrypt($validated['key']),
            'model' => $validated['model'],
            'monthly_token_limit' => $validated['monthly_token_limit'],
            'priority' => $validated['priority'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('ai-assistant.keys.index')
            ->with('success', 'API key added successfully');
    }

    /**
     * Show edit form
     */
    public function edit(AiApiKey $apiKey)
    {
        $providers = AiProvider::where('is_active', true)->get();

        return view('plugins/ai-assistant::admin.keys.edit', [
            'apiKey' => $apiKey,
            'providers' => $providers,
        ]);
    }

    /**
     * Update API key
     */
    public function update(Request $request, AiApiKey $apiKey): RedirectResponse
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:ai_providers,id',
            'label' => 'nullable|string|max:255',
            'key' => 'nullable|string|min:10',
            'model' => 'nullable|string|max:255',
            'monthly_token_limit' => 'nullable|integer|min:0',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'reset_token_usage' => 'boolean',
        ]);

        $data = [
            'provider_id' => $validated['provider_id'],
            'label' => $validated['label'],
            'model' => $validated['model'],
            'monthly_token_limit' => $validated['monthly_token_limit'],
            'priority' => $validated['priority'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ];

        // Update key if provided
        if ($validated['key'] ?? null) {
            $provider = AiProvider::findOrFail($validated['provider_id']);
            
            // Create temporary API key object for validation
            $tempKey = new AiApiKey([
                'provider_id' => $provider->id,
                'key_encrypted' => encrypt($validated['key']),
                'model' => $validated['model'] ?? null,
            ]);
            $tempKey->setRelation('provider', $provider);

            if (!$this->aiService->validateApiKey($tempKey)) {
                return back()
                    ->withInput()
                    ->with('error', 'Invalid API key for ' . $provider->display_name);
            }

            $data['key_encrypted'] = encrypt($validated['key']);
        }

        if ($validated['reset_token_usage'] ?? false) {
            $data['monthly_tokens_used'] = 0;
            $data['tokens_reset_at'] = now();
        }

        $apiKey->update($data);

        return redirect()
            ->route('ai-assistant.keys.index')
            ->with('success', 'API key updated successfully');
    }

    /**
     * Delete API key
     */
    public function destroy(AiApiKey $apiKey): RedirectResponse
    {
        $apiKey->delete();

        return redirect()
            ->route('ai-assistant.keys.index')
            ->with('success', 'API key deleted successfully');
    }

    /**
     * Toggle API key status
     */
    public function toggleStatus(AiApiKey $apiKey)
    {
        $apiKey->update(['is_active' => !$apiKey->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $apiKey->is_active,
        ]);
    }

    /**
     * Reorder API keys for priority
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'keys' => 'required|array',
            'keys.*' => 'integer|exists:ai_api_keys,id',
        ]);

        foreach ($validated['keys'] as $priority => $keyId) {
            AiApiKey::where('id', $keyId)->update(['priority' => $priority]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get available models for a specific provider
     * Fetches dynamically from provider API when API key is available
     */
    public function getProviderModels($providerId)
    {
        try {
            $providerId = (int) $providerId;
            $provider = AiProvider::findOrFail($providerId);
            
            // Debug: Log the provider info
            \Log::debug("Fetching models for provider", [
                'provider_id' => $provider->id,
                'provider_name' => $provider->name,
                'provider_display_name' => $provider->display_name,
            ]);
            
            // Initialize model fetchers
            ModelFetcherFactory::initialize();

            // Try to fetch live models from provider API
            $fetchedModels = $this->fetchModelsFromProvider($provider);

            // Merge with any previously used models from database
            $dbModels = AiApiKey::query()
                ->where('provider_id', $providerId)
                ->where('model', '!=', null)
                ->distinct()
                ->pluck('model')
                ->toArray();

            // Combine and deduplicate (API models first, then DB models)
            $allModels = array_unique(array_merge($fetchedModels, $dbModels));

            // If no models found, return empty array (not an error)
            $models = !empty($allModels) ? array_values($allModels) : [];

            \Log::debug("Models found", [
                'api_models_count' => count($fetchedModels),
                'db_models_count' => count($dbModels),
                'total_models_count' => count($models),
                'models' => array_slice($models, 0, 5), // First 5 for logging
            ]);

            return response()->json([
                'success' => true,
                'provider_id' => $provider->id,
                'provider_name' => $provider->display_name,
                'models' => $models,
            ]);
        } catch (\Exception $e) {
            logger()->error("Error fetching models for provider: " . $e->getMessage(), [
                'exception' => $e,
                'provider_id' => $providerId ?? null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch models: ' . $e->getMessage(),
                'models' => [],
            ], 500);
        }
    }

    /**
     * Fetch models from provider API with caching
     */
    private function fetchModelsFromProvider(AiProvider $provider): array
    {
        // Try to get an active API key for this provider
        $apiKey = AiApiKey::query()
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            // No API key available, return empty (will fall back to DB models)
            return [];
        }

        try {
            $decryptedKey = $apiKey->getDecryptedKey();
            $cacheService = new ModelCacheService();
            
            // Get models with caching (1 hour cache duration)
            $models = $cacheService->getModels($provider, $decryptedKey);
            
            return $models;
        } catch (\Exception $e) {
            logger()->warning("Failed to fetch models from {$provider->display_name}: " . $e->getMessage());
            return [];
        }
    }
}
