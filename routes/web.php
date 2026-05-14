<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\GuestMessageController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

RateLimiter::for('guest-message', function (Request $request) {
    return Limit::perDay(2)->by($request->ip() . $request->route('store_slug'))->response(function () {
        return response()->json(['message' => 'Bugün için limitine ulaştın.'], 429);
    });
});

Route::get('/api/{store_slug}/now-playing', [MenuController::class, 'nowPlaying'])->name('api.now_playing');
Route::post('/api/{store_slug}/message', [GuestMessageController::class, 'store'])->middleware('throttle:guest-message');

Route::middleware([\App\Http\Middleware\TrackVisitor::class])->group(function () {
    Route::get('/{store_slug}', [MenuController::class, 'index'])->name('menu.index');

    Route::prefix('tracking')->group(function () {
        Route::post('/hit', [\App\Http\Controllers\TrackingController::class, 'hit']);
        Route::post('/fingerprint', [\App\Http\Controllers\TrackingController::class, 'fingerprint']);
        Route::post('/vote', [\App\Http\Controllers\TrackingController::class, 'toggleVote']);
    });
});

Route::get('/', function () { 
    // Temporary redirect to gorukle for dev 
    return redirect('/gorukle'); 
});

