<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\GuestMessageController;
use App\Http\Controllers\PollController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware([\App\Http\Middleware\TrackVisitor::class])->group(function () {
    Route::get('/api/{store_slug}/now-playing', [MenuController::class, 'nowPlaying'])->name('api.now_playing');
    Route::post('/api/{store_slug}/message', [GuestMessageController::class, 'store'])->middleware('throttle:guest-message');
    
    // Google Review Interaction Funnel
    Route::prefix('api/{store_slug}/review-interaction')->group(function () {
        Route::post('/', [\App\Http\Controllers\GoogleReviewInteractionController::class, 'store']);
        Route::patch('/{id}', [\App\Http\Controllers\GoogleReviewInteractionController::class, 'update']);
        Route::post('/{id}/google-clicked', [\App\Http\Controllers\GoogleReviewInteractionController::class, 'googleClicked']);
    });

    Route::prefix('api/{store_slug}/polls')->group(function () {
        Route::get('/active', [PollController::class, 'active']);
        Route::get('/', [PollController::class, 'index']);
        Route::post('/{poll}/vote', [PollController::class, 'vote'])->middleware('throttle:poll-vote');
    });

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

