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
        // Keyed by the visitor cookie, not the IP: everyone on the venue's
        // wifi shares one public IP and would otherwise share one quota.
        RateLimiter::for('guest-message', function (Request $request) {
            $slug = $request->route('store_slug');
            $visitor = $request->cookie('qr_menu_visitor_id') ?: $request->ip();

            return [
                Limit::perDay(2)->by("gm-visitor:{$visitor}:{$slug}")->response(function () {
                    return response()->json(['message' => 'Bugün için limitine ulaştın.'], 429);
                }),
                Limit::perHour(20)->by("gm-ip:{$request->ip()}:{$slug}")->response(function () {
                    return response()->json(['message' => 'Çok fazla istek geldi, biraz sonra tekrar dene.'], 429);
                }),
            ];
        });

        RateLimiter::for('poll-vote', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . $request->route('store_slug'))->response(function () {
                return response()->json(['message' => 'Çok fazla oy kullandın.'], 429);
            });
        });
    }
}
