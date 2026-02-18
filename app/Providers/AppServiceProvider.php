<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

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
        // Trust X-Forwarded-* headers from proxy (important for HTTPS behind nginx)
        if (env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        RateLimiter::for('answer', function (Request $request) {
            \Log::info("The generated rate limit key is: " . md5('answer' . $request->ip()));
            \Log::info('Rate limiter hit by: ' . $request->ip());

            return Limit::perMinutes(30, 2)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'rate limit exceeded',
                    ], 429);
                });
        });
    }
}
