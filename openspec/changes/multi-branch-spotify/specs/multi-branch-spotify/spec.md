## ADDED Requirements

### Requirement: Database Store Credentials
The `stores` database table must be able to securely store Spotify API credentials specific to a given branch.

#### Scenario: Admin views Store Form
- **WHEN** an administrator views the Filament Store Edit Form
- **THEN** they see fields for `spotify_client_id`, `spotify_client_secret`, and `spotify_refresh_token` under a "Spotify Integration" section. `secret` and `refresh_token` are formatted as password fields.

#### Scenario: Data Encryption
- **WHEN** the Store model persists or updates `spotify_client_secret` or `spotify_refresh_token`
- **THEN** those fields are automatically encrypted in the actual database via Laravel cast mechanisms.

### Requirement: Service Separation
The `SpotifyService` must dynamically retrieve credentials instead of using global configurations.

#### Scenario: Service Fetches Token
- **WHEN** `SpotifyService->getNowPlaying(Store $store)` is called
- **THEN** it looks for the access token in Cache using the key `spotify_access_token_{$store->id}`. If not found, it refreshes the token using the Store's refresh token and client ID/secret, explicitly failing cleanly if credentials are missing or invalid, then returning `null`.

#### Scenario: Service API Fallback
- **WHEN** the `SpotifyService` requests now playing for a specific store that has no active music playback
- **THEN** it falls back to the Store's specifically recently played items or returns `null` if nothing was played.

### Requirement: Endpoint Scoping
The frontend API endpoint for checking Spotify playback must distinguish between active stores.

#### Scenario: React Polling
- **WHEN** the `Index.jsx` renders the Now Playing component
- **THEN** it consistently queries the internal API endpoint for the branch (which may already be scoped via Controller dependency injection/router parameter), avoiding cross-pollination.
