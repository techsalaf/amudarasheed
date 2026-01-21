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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Push our middleware globally so it applies on all requests
        $this->app->make(HttpKernel::class)->pushMiddleware(SubdirectoryFix::class);
    }
}
