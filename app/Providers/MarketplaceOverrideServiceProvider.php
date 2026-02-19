<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CustomMarketplaceService;
use Botble\PluginManagement\Services\MarketplaceService;

class MarketplaceOverrideServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override MarketplaceService with our custom implementation only if the custom class exists
        // Use bind instead of singleton to match Laravel's default behavior
        if (class_exists(CustomMarketplaceService::class)) {
            $this->app->bind(MarketplaceService::class, function ($app) {
                return new CustomMarketplaceService();
            });
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
