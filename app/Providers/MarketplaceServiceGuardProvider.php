<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Botble\PluginManagement\Services\MarketplaceService;

/**
 * Guard provider that ensures MarketplaceService initialization doesn't fail
 * due to missing configuration keys.
 */
class MarketplaceServiceGuardProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Ensure marketplace config keys exist with sensible defaults
        $this->app->booting(function () {
            // Provide safe defaults to prevent undefined array key errors
            config([
                'packages.plugin-management.general.marketplace_url' => config('marketplace.url', 'https://marketplace.botble.com'),
                'packages.plugin-management.general.marketplace_token' => config('marketplace.token', 'guest-token'),
                'packages.plugin-management.general.marketplace_product_id' => config('marketplace.product_id', ''),
                'packages.plugin-management.general.marketplace_license_url' => config('marketplace.license_url', 'https://api.botble.com'),
                'packages.plugin-management.general.marketplace_license_api_key' => config('marketplace.license_api_key', ''),
            ]);
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
