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
        // Override MarketplaceService with our custom implementation
        // Use bind instead of singleton to match Laravel's default behavior
        $this->app->bind(MarketplaceService::class, function ($app) {
            return new CustomMarketplaceService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
