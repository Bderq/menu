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

    public function nowPlaying()
    {
        return \Illuminate\Support\Facades\Cache::remember('now_playing', 10, function () {
            $apiKey = env('LASTFM_API_KEY');
            $user = env('LASTFM_USER');

            if (!$apiKey || !$user) {
                return null;
            }

            try {
                $response = \Illuminate\Support\Facades\Http::get("https://ws.audioscrobbler.com/2.0/", [
                    'method' => 'user.getrecenttracks',
                    'user' => $user,
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'limit' => 1
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $track = $data['recenttracks']['track'][0] ?? null;

                    if ($track) {
                        return [
                            'artist' => $track['artist']['#text'] ?? 'Unknown',
                            'track' => $track['name'] ?? 'Unknown',
                            'album' => $track['album']['#text'] ?? '',
                            'image' => $track['image'][2]['#text'] ?? '', // medium size
                            'is_playing' => isset($track['@attr']['nowplaying']) && $track['@attr']['nowplaying'] === 'true'
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Silently fail or log
            }

            return null;
        });
    }
}
