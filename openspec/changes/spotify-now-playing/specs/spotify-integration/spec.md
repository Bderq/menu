## ADDED Requirements

### Requirement: Fetch currently playing track
The backend SHALL fetch the currently or most recently playing track from the configured Spotify account using the credentials stored in the environment (`.env`).

#### Scenario: Active playback
- **WHEN** the venue's Spotify account is playing a song
- **THEN** the system returns the track name, artist(s) name, and cover art image URL.

#### Scenario: Inactive playback but recent history exists
- **WHEN** nothing is playing currently
- **THEN** the system fetches the most recently played track instead.

### Requirement: Secure Authentication and Token Refresh
The system SHALL use a stored refresh token to request temporary access tokens from the Spotify API.

#### Scenario: Access token expiration
- **WHEN** the current Spotify access token expires or is missing
- **THEN** the system uses the `SPOTIFY_REFRESH_TOKEN` to retrieve a new token before making the track request.

### Requirement: Request Caching
The system SHALL cache the fetched track data for a defined period (e.g., 30-60 seconds) to avoid hitting external Spotify API rate limits.

#### Scenario: High concurrency requests
- **WHEN** 50 customers view the menu within the same 30-second window
- **THEN** the Spotify API is queried exactly once, and the other 49 requests are served from the internal cache.
