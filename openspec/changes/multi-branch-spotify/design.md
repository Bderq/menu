## Context

Currently, `SpotifyService` is tightly coupled with global `.env` values (`services.spotify.client_id`, etc.). This means all active stores show the identical "Now Playing" track across the platform.

## Goals / Non-Goals

**Goals:**
- Provide a scalable architecture that allows each branch to connect its own Spotify account.
- Enable branch administrators to input these credentials directly into Filament without accessing the server `.env`.
- Ensure token caching remains isolated between branches.

**Non-Goals:**
- We are NOT implementing complete OAuth flows within Filament right now. The admin must manually generate the refresh token from their Spotify Developer App. 

## Decisions

- **Store Model Modification**: We will add `spotify_client_id`, `spotify_client_secret`, and `spotify_refresh_token` directly to the `stores` table using the `encrypted` cast. Encryption is vital to guarantee that leaked database exports don't publicly reveal Spotify API secrets.
- **Service Re-scoping**: `SpotifyService`'s methods will no longer rely on `__construct` initializing global config values. Instead, its methods will become pure and stateless (e.g. `getNowPlaying(Store $store)`), retrieving credentials directly from the passed Store definition.
- **API Endpoint Update**: `Routes/web.php` and `MenuController` will handle fetching the specific store using its slug, passing it into `SpotifyService`, and the React frontend will make polling requests using the specific URL parameters corresponding to the active store.

## Risks / Trade-offs

- **Risk**: Admins may find it technically difficult to generate independent `refresh_token`s. 
- **Trade-off**: The manual process sacrifices a degree of "Plug-and-play" UX for significant implementation speed, avoiding a complex Spotify OAuth flow integration which would otherwise slow down release significantly.
- **Cache Contamination Risk**: `Cache::remember` keys must explicitly include the `$store->id` or `$store->slug`, otherwise branches might temporarily show playing songs from other branches due to key collisions.
