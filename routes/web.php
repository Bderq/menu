<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/api/now-playing', [MenuController::class, 'nowPlaying'])->name('api.now_playing');
Route::get('/{store_slug}', [MenuController::class, 'index'])->name('menu.index');
Route::get('/', function () { 
    // Temporary redirect to kadikoy for dev 
    return redirect('/kadikoy'); 
});

