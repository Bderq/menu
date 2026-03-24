## ADDED Requirements

### Requirement: Dynamic "Now Playing" UI Component
The frontend SHALL display a "Now Playing" component above the sidebar or in an appropriate visible area on the menu layout, utilizing data fetched from the backend Spotify endpoint.

#### Scenario: Successful data fetch
- **WHEN** the frontend successfully polls the `/api/spotify/now-playing` endpoint and receives track data
- **THEN** the component updates to display the current track's album art, track name, and artist name in a style consistent with the brutalist/sticker visual identity of the menu.

#### Scenario: Playback stopped or error
- **WHEN** the endpoint fails or returns that no track is available
- **THEN** the component gracefully hides itself or displays a fallback "Venue Radio" state without breaking the layout.

#### Scenario: Live polling
- **WHEN** the user stays on the menu page for an extended period
- **THEN** the frontend component requests new data periodically (e.g., every 30 seconds) and transitions smoothly if the song changes.
