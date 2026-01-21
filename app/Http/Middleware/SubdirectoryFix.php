<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SubdirectoryFix
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply the fix if APP_URL contains "/dev"
        $appUrl = config('app.url');
        if (str_contains($appUrl, '/dev')) {
        // Force Laravel to treat "/dev" as the base folder
            $_SERVER['SCRIPT_NAME'] = str_replace('/dev/', '/', $_SERVER['SCRIPT_NAME']);
            $_SERVER['REQUEST_URI'] = preg_replace('#^/dev#', '', $_SERVER['REQUEST_URI']);
        }

        return $next($request);
    }
}
