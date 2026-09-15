<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\GuestMessageController;
use App\Http\Controllers\PollController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\TrackVisitor::class])->group(function () {
    // 10s polling — must not touch visitor/visit tracking on every tick
    Route::get('/api/{store_slug}/now-playing', [MenuController::class, 'nowPlaying'])
        ->withoutMiddleware(\App\Http\Middleware\TrackVisitor::class)
        ->name('api.now_playing');
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

Route::post('/telegram/webhook', \App\Http\Controllers\TelegramWebhookController::class)->name('telegram.webhook');

Route::get('/store-tables/print', \App\Http\Controllers\StoreTablePrintController::class)
    ->middleware('auth')
    ->name('store-tables.print');

