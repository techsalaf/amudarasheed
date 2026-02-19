<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Botble\PluginManagement\Services\MarketplaceService;

class MarketplaceOverrideServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Try to use custom implementation if available; gracefully fall back to default
        try {
            $customServiceClass = 'App\\Services\\CustomMarketplaceService';
            if (class_exists($customServiceClass)) {
                $this->app->bind(MarketplaceService::class, function ($app) use ($customServiceClass) {
                    return new $customServiceClass();
                });
            }
        } catch (\Throwable $e) {
            // Silently fail - Botble's default service will be used
            // This prevents deployment issues when custom service is unavailable
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
