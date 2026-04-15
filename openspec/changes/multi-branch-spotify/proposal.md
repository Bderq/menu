## Why

Currently, the Spotify "Now Playing" feature relies on a single, globally defined Spotify account via `.env` credentials. As the QR Menu application expands to multiple branches (stores), each branch needs to display the music currently playing in its specific physical location, managed independently through its own Spotify account.

## What Changes

We will introduce a database-backed storage approach for Spotify credentials inside the `stores` table. Branch managers will be able to manage these credentials in the Filament admin panel. The `SpotifyService` and corresponding controllers will be updated to accept a `Store` instance to dynamically fetch tokens on a per-branch basis. The frontend `NowPlaying` AJAX endpoint will also be updated to ensure the branch slug is correctly scoped.

## Capabilities

### New Capabilities
- `multi-branch-spotify`: Ability to authenticate and retrieve Spotify's currently playing track on a per-store basis using isolated credentials.

### Modified Capabilities
- `qr-menu-frontend`: Updates the "Now Playing" functionality to request data specific to the loaded store.

## Impact

- `stores` table (requires new migrations)
- `App\Models\Store`
- `App\Services\SpotifyService`
- `App\Http\Controllers\MenuController` (nowPlaying method)
- Filament `StoreResource` (form modification)
- Frontend Javascript responsible for polling the /now-playing endpoint
