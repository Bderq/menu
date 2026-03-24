## 1. Environment & Setup

- [x] 1.1 Add Spotify credential placeholders (`SPOTIFY_CLIENT_ID`, `SPOTIFY_CLIENT_SECRET`, `SPOTIFY_REFRESH_TOKEN`) to `.env.example`.
- [x] 1.2 Generate a mock or empty `app/Services/SpotifyService.php` to handle Spotify API calls.

## 2. Backend Implementation

- [x] 2.1 Implement an access token fetcher in `SpotifyService` that exchanges `SPOTIFY_REFRESH_TOKEN` for a valid short-lived access token.
- [x] 2.2 Implement fetching the currently playing track from Spotify API (`GET /v1/me/player/currently-playing` or fallback to `/v1/me/player/recently-played`).
- [x] 2.3 Implement caching in `SpotifyService` to store the track data result for 30-60 seconds to avoid violating Spotify API rate limits.
- [x] 2.4 Create `SpotifyController` and configure a new route `GET /api/spotify/now-playing` to return the cached track data.

## 3. Frontend Implementation

- [x] 3.1 Locate the existing "Now Playing" UI component in the layout (likely above the sidebar in `MenuInteractionDrawer` or `StreetLayout`).
- [x] 3.2 Update the React component to utilize a `useEffect` hook to poll `/api/spotify/now-playing` every 30 seconds.
- [x] 3.3 Integrate the fetched track data (album art, track, artist name) into the "Now Playing" UI.
- [x] 3.4 Refine the styling of the UI component to securely match the site's Brutalist Sticker Style, ensuring smooth CSS transitions when the song updates.
