## Why

The venue wants to display the currently playing Spotify song on the digital QR menu to enhance the atmosphere and provide a premium, modern feel to their digital menu experience. This engages the customers and connects the physical venue's ambiance with the digital interface.

## What Changes

- Add Spotify API integration to periodically fetch the currently playing or most recently playing song for the venue's Spotify account.
- Since the venue's own Spotify will be used, we will store the required Spotify API credentials (client ID, client secret, and refresh token) securely via `.env` variables.
- Integrate the fetched song data into the existing "Now Playing" UI component located above the sidebar.
- Display the last played song (or currently playing) along with basic track details (like track name, artist name, and album art).

## Capabilities

### New Capabilities
- `spotify-integration`: Backend capability to authenticate with Spotify API using a refresh token, fetch the currently or most recently playing track, and provide this data via an API endpoint or Inertia shared props.

### Modified Capabilities
- `qr-menu-frontend`: Update the existing layout's sidebar "Now Playing" component to consume the live/fetched Spotify data instead of static or placeholder data.

## Impact

- **Backend**: New service or class for Spotify API interactions. Likely requires caching to avoid hitting Spotify API rate limits on every page load.
- **Environment**: Addition of Spotify-related `.env` variables (`SPOTIFY_CLIENT_ID`, `SPOTIFY_CLIENT_SECRET`, `SPOTIFY_REFRESH_TOKEN`).
- **Frontend**: The existing "Now Playing" UI component above the sidebar will need to be updated to render dynamic data.
