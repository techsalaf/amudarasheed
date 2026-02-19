<?php

namespace Botble\AiAssistant\Services;

use Botble\AiAssistant\Models\AiSetting;

class AiSettingsService
{
    protected array $defaults = [
        'enable_ai' => true,
        'default_model' => null,
        'enable_text_generation' => true,
        'enable_image_generation' => false,
        'enable_pii_protection' => true,
        'max_tokens_per_request' => 2000,
        'temperature' => '0.7',
        'provider_priority' => 'json',
        'enable_usage_tracking' => true,
        'auto_reset_tokens_monthly' => true,
        'enable_for_posts' => true,
        'enable_for_pages' => true,
        'enable_for_products' => true,
        'enable_for_seo_fields' => true,
        'enable_for_custom_fields' => true,
    ];

    /**
     * Get all settings
     */
    public function getAllSettings(): array
    {
        $settings = [];
        foreach ($this->defaults as $key => $default) {
            $settings[$key] = $this->getSetting($key, $default);
        }

        return $settings;
    }

    /**
     * Get a single setting
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return AiSetting::get($key, $default);
    }

    /**
     * Update settings
     */
    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            $type = $this->getSettingType($key);
            AiSetting::set($key, $value, $type);
        }
    }

    /**
     * Get provider priority/ordering
     */
    public function getProviderPriority(): array
    {
        $priority = $this->getSetting('provider_priority', []);
        
        if (is_string($priority)) {
            $priority = json_decode($priority, true) ?? [];
        }

        return $priority ?? [];
    }

    /**
     * Set provider priority/ordering
     */
    public function setProviderPriority(array $priority): void
    {
        AiSetting::set('provider_priority', $priority, 'json');
    }

    /**
     * Determine setting type for storage
     */
    protected function getSettingType(string $key): string
    {
        return match ($key) {
            'provider_priority' => 'json',
            'max_tokens_per_request', 'temperature' => 'string',
            'enable_ai', 'enable_text_generation', 'enable_image_generation',
            'enable_pii_protection', 'auto_reset_tokens_monthly',
            'enable_usage_tracking', 'enable_for_posts', 'enable_for_pages',
            'enable_for_products', 'enable_for_seo_fields', 'enable_for_custom_fields' => 'boolean',
            default => 'string',
        };
    }

    /**
     * Reset all settings to defaults
     */
    public function resetToDefaults(): void
    {
        foreach ($this->defaults as $key => $value) {
            $type = $this->getSettingType($key);
            AiSetting::set($key, $value, $type);
        }
    }
}
