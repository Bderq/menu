<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/api/now-playing', [MenuController::class, 'nowPlaying'])->name('api.now_playing');
Route::get('/{store_slug}', [MenuController::class, 'index'])->name('menu.index');

Route::prefix('tracking')->group(function () {
    Route::post('/hit', [\App\Http\Controllers\TrackingController::class, 'hit']);
    Route::post('/fingerprint', [\App\Http\Controllers\TrackingController::class, 'fingerprint']);
});

Route::get('/', function () { 
    // Temporary redirect to gorukle for dev 
    return redirect('/gorukle'); 
});

