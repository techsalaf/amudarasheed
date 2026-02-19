<?php

namespace Botble\AiAssistant\Http\Controllers;

use Botble\AiAssistant\Models\AiProvider;
use Botble\AiAssistant\Models\AiSetting;
use Botble\AiAssistant\Services\AiSettingsService;
use Illuminate\Http\Request;

class AiSettingsController
{
    public function __construct(
        protected AiSettingsService $settingsService
    ) {}

    /**
     * Show settings page
     */
    public function index()
    {
        $settings = $this->settingsService->getAllSettings();
        $providers = AiProvider::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        $providerPriority = $this->settingsService->getProviderPriority();

        return view('plugins/ai-assistant::admin.settings.index', [
            'settings' => $settings,
            'providers' => $providers,
            'providerPriority' => $providerPriority,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'enable_ai' => 'boolean',
            'default_model' => 'nullable|string',
            'enable_text_generation' => 'boolean',
            'enable_image_generation' => 'boolean',
            'enable_pii_protection' => 'boolean',
            'max_tokens_per_request' => 'integer|min:100|max:10000',
            'temperature' => 'numeric|min:0|max:2',
            'enable_usage_tracking' => 'boolean',
            'auto_reset_tokens_monthly' => 'boolean',
            'enable_for_posts' => 'boolean',
            'enable_for_pages' => 'boolean',
            'enable_for_products' => 'boolean',
            'enable_for_seo_fields' => 'boolean',
            'enable_for_custom_fields' => 'boolean',
            'provider_priority' => 'nullable|array',
        ]);

        $this->settingsService->updateSettings($validated);

        if ($validated['provider_priority'] ?? null) {
            $this->settingsService->setProviderPriority($validated['provider_priority']);
        }

        return redirect()
            ->route('ai-assistant.settings.index')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Reset settings to defaults
     */
    public function reset(Request $request)
    {
        $this->settingsService->resetToDefaults();

        return redirect()
            ->route('ai-assistant.settings.index')
            ->with('success', 'Settings reset to defaults');
    }
}
