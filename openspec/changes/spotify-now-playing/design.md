## Context

The venue wants to share its atmosphere with the digital users via the QR menu by displaying the currently playing or most recently played Spotify track. Since the venue uses a single main Spotify account, its credentials will be used rather than individual user accounts. To achieve this securely, we need an integration with the Spotify API utilizing a refresh token stored in the `.env` file since this is a venue-wide integration.

## Goals / Non-Goals

**Goals:**
- Read the currently playing or most recently played track from the venue's Spotify account.
- Pass this data to the frontend, primarily the existing "Now Playing" UI component above the sidebar.
- Run this integration seamlessly without hitting Spotify API rate limits.
- Securely store API credentials using `.env` variables.

**Non-Goals:**
- User-specific Spotify authentication (OAuth login flow for customers).
- Playback control (play/pause/skip) from the menu interface.
- Displaying full playlists or upcoming queues.

## Decisions

- **Backend Polling & Caching**: We will create a `SpotifyService` in Laravel. Instead of fetching from Spotify on every single page load or request, the service will cache the result for a short duration (e.g., 30-60 seconds) in Redis or File cache. This prevents rate limiting if many customers access the menu simultaneously.
- **Credential Storage**: `SPOTIFY_CLIENT_ID`, `SPOTIFY_CLIENT_SECRET`, and `SPOTIFY_REFRESH_TOKEN` will be stored in `.env`. The `SpotifyService` will use the refresh token to get a short-lived access token when fetching the track.
- **Frontend Integration**: We will implement a lightweight API endpoint (`/api/spotify/now-playing`) and poll it from the "Now Playing" UI component on the client side every 30 seconds. This is better than relying on Inertia page-load props, which can go stale if the user leaves the page open. The UI will have a specialized, elegant look according to `frontend-specialist` deep design guidelines (no default templates).

## Risks / Trade-offs

- **Spotify API Rate Limits** → **Mitigation**: Implement robust backend caching. Only one background/backend request happens per cache window.
- **Token Expiry Issues** → **Mitigation**: The system must automatically fetch a new access token using the `.env` refresh token when the service needs to authenticate. If the refresh token itself becomes invalid, handle the failure gracefully on the frontend (hide the component or show offline state).
