<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/api/{store_slug}/now-playing', [MenuController::class, 'nowPlaying'])->name('api.now_playing');
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

