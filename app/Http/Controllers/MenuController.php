<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index($store_slug, \App\Services\MenuService $menuService)
    {
        $store = \App\Models\Store::where('slug', $store_slug)->firstOrFail();

        $menuData = $menuService->getFormattedMenuData($store);

        return Inertia::render('Menu/Index', [
            'menuData' => $menuData,
            'store' => $store
        ]);
    }

    public function nowPlaying(\App\Services\SpotifyService $spotifyService)
    {
        try {
            $track = $spotifyService->getNowPlaying();

            if ($track) {
                return [
                    'artist' => $track['artist'] ?? 'Unknown',
                    'track' => $track['name'] ?? 'Unknown',
                    'album' => $track['album'] ?? '',
                    'duration' => $track['duration'] ?? '00:00',
                    'duration_ms' => $track['duration_ms'] ?? 0,
                    'progress_ms' => $track['progress_ms'] ?? 0,
                    'image' => $track['album_art'] ?? '',
                    'is_playing' => $track['is_playing'] ?? false,
                    'url' => $track['url'] ?? '',
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('NowPlaying fetch failed: ' . $e->getMessage());
        }

        return null;
    }
}
