## Why

The QR menu currently captures guest feedback only through open-ended messages ("Ses Ver") and passive product likes. A structured poll system would let operators collect targeted, measurable insights from guests — tied to specific time windows, branches, and service contexts — turning anecdotal feedback into actionable data.

## What Changes

- Operators can create polls with multiple question types (single choice, emoji reaction, 1-5 star rating)
- Each poll has a configurable schedule: date range, daily time window (e.g. 18:00–22:00), and optional days-of-week
- Polls can be scoped to all branches or a specific store
- A "show once per visitor" toggle controls whether the popup appears only once per device fingerprint
- When multiple polls overlap in time, a fair-rotation algorithm picks which one to display (prioritising polls not shown the previous day)
- After voting, the guest immediately sees live percentage results with animated bars
- Guests can access all currently active polls at any time via a dedicated **Anket** tab in the menu drawer
  - "Show once" controls the **automatic popup only** — the drawer tab always shows active polls
  - Already-voted polls display results; unvoted polls can still be answered from the drawer
- A dedicated **admin page** (`/admin/polls`) shows all polls, live result breakdowns per branch, and archived polls

## Capabilities

### New Capabilities

- `poll-management`: Admin CRUD for polls — question, type, options, schedule (date range + daily time window + days of week), store scope, show-once toggle, priority, and active/archive status
- `poll-scheduling`: Server-side logic that resolves which poll(s) are currently active for a given store and time, implements fair-rotation when multiple polls overlap, and records daily display history
- `poll-submission`: Guest-facing API to fetch the active poll (filtered by visitor fingerprint impression history) and submit a vote; returns live result percentages immediately after voting
- `poll-results-dashboard`: Filament admin page showing per-poll vote breakdowns, filterable by branch and date range, with archived poll history
- `poll-drawer-tab`: Frontend React tab in the menu drawer listing active polls for the current store, showing vote state (unvoted / voted + results / expired)

### Modified Capabilities

- `qr-menu-frontend`: Add **Anket** tab to the existing bottom drawer navigation

## Impact

- **New tables**: `polls`, `poll_schedules`, `poll_options`, `poll_votes`, `poll_impressions`
- **New API routes**: `GET /api/{store}/polls/active`, `POST /api/{store}/polls/{poll}/vote`
- **New Filament page**: `PollDashboard` with result widgets
- **Frontend**: New drawer tab component + poll popup component + result visualization
- **Existing**: `visitors` table referenced for fingerprint-based impression tracking (read-only)
