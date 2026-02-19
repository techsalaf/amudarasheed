<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use App\Http\Middleware\SubdirectoryFix;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override MarketplaceService to bypass credential validation
        $this->app->bind(
            \Botble\PluginManagement\Services\MarketplaceService::class,
            \App\Services\CustomMarketplaceService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Push our middleware globally so it applies on all requests
        $this->app->make(HttpKernel::class)->pushMiddleware(SubdirectoryFix::class);

        // Override marketplace routes to use our custom controller
        if (config('packages.plugin-management.general.enable_marketplace_feature', true)) {
            $this->app->bind(
                \Botble\PluginManagement\Http\Controllers\MarketplaceController::class,
                \App\Http\Controllers\CustomMarketplaceController::class
            );
        }
    }
}
