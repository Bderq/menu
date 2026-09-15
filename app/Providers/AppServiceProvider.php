<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Must live here, not in routes/web.php: with a cached route file
        // those definitions never run and the throttle middleware 500s.
        RateLimiter::for('guest-message', function (Request $request) {
            return Limit::perDay(2)->by($request->ip() . $request->route('store_slug'))->response(function () {
                return response()->json(['message' => 'Bugün için limitine ulaştın.'], 429);
            });
        });

        RateLimiter::for('poll-vote', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . $request->route('store_slug'))->response(function () {
                return response()->json(['message' => 'Çok fazla oy kullandın.'], 429);
            });
        });
    }
}
