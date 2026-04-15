<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    public function __construct()
    {
        // Credentials are now retrieved dynamically from the Store model
    }

    /**
     * Get a valid access token for a specific store.
     */
    private function getAccessToken(\App\Models\Store $store): ?string
    {
        return Cache::remember("spotify_access_token_{$store->id}", 3500, function () use ($store) {
            $clientId = $store->spotify_client_id;
            $clientSecret = $store->spotify_client_secret;
            $refreshToken = $store->spotify_refresh_token;

            if (!$clientId || !$clientSecret || !$refreshToken) {
                Log::warning("Spotify credentials are not fully configured for store: {$store->name}");
                return null;
            }

            $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)
                ->post('https://accounts.spotify.com/api/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error("Spotify token refresh failed for store: {$store->name}", ['error' => $response->body()]);
            return null;
        });
    }

    /**
     * Get the now playing or recently played track for a specific store.
     */
    public function getNowPlaying(\App\Models\Store $store): ?array
    {
        return Cache::remember("spotify_now_playing_{$store->id}", 10, function () use ($store) {
            $token = $this->getAccessToken($store);
            
            if (!$token) {
                return null;
            }

            $response = Http::withToken($token)
                ->get('https://api.spotify.com/v1/me/player/currently-playing');

            if ($response->successful() && $response->status() === 200) {
                $data = $response->json();
                
                if (isset($data['item'])) {
                    $item = $data['item'];
                    $item['progress_ms'] = $data['progress_ms'] ?? 0;
                    return $this->formatTrackData($item, true);
                }
            }

            // Fallback to recently played
            $recentResponse = Http::withToken($token)
                ->get('https://api.spotify.com/v1/me/player/recently-played', [
                    'limit' => 1
                ]);

            if ($recentResponse->successful() && !empty($recentResponse->json('items'))) {
                $track = $recentResponse->json('items')[0]['track'];
                return $this->formatTrackData($track, false);
            }

            return null;
        });
    }

    /**
     * Format the track data for the frontend
     */
    private function formatTrackData(array $track, bool $isPlaying): array
    {
        $durationMs = $track['duration_ms'] ?? 0;
        $minutes = floor($durationMs / 60000);
        $seconds = floor(($durationMs % 60000) / 1000);
        $duration = sprintf('%02d:%02d', $minutes, $seconds);

        return [
            'is_playing' => $isPlaying,
            'name' => $track['name'] ?? 'Unknown',
            'artist' => collect($track['artists'] ?? [])->pluck('name')->implode(', '),
            'album_art' => $track['album']['images'][0]['url'] ?? null,
            'album' => $track['album']['name'] ?? '',
            'duration' => $duration,
            'duration_ms' => (int) $durationMs,
            'progress_ms' => (int) ($track['progress_ms'] ?? 0),
            'url' => $track['external_urls']['spotify'] ?? null,
        ];
    }
}
