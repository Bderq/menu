## Context

The QR menu system already tracks visitors via device fingerprints (`visitors` table) and records detailed interaction analytics. The "Ses Ver" open-ended feedback system exists but provides unstructured data. This design introduces a structured poll system built on the same fingerprint infrastructure, reusing `visitor_id` for impression tracking.

Key existing constraints:
- Frontend: React 19 + Inertia.js 2, served via a bottom-drawer navigation pattern
- Backend: Laravel 12, MySQL, Filament 5 for admin
- Visitor fingerprinting already in place — no new client-side fingerprinting needed

## Goals / Non-Goals

**Goals:**
- Admins can create and schedule polls with time-window and branch scoping
- Guest popup appears once per visitor (if "show once" enabled) — tracked via fingerprint
- Fair rotation resolves overlapping active polls
- Voting returns live results immediately
- Drawer tab always exposes active polls regardless of popup impression state
- Dedicated Filament admin results page

**Non-Goals:**
- Conditional branching logic (if answer X → show question Y)
- Anonymous result hiding (results always shown after vote)
- Push notifications when poll milestones are reached (future)
- Product/category-linked polls (future)

## Decisions

### D1 — Data Model: Separate `poll_schedules` table (not JSON on `polls`)

**Decision**: Schedule configuration lives in its own `poll_schedules` table (one-to-many with `polls`).

**Rationale**: A single poll may need multiple time windows (e.g., lunch 12-14 AND dinner 19-22). Storing schedules as rows is queryable with standard SQL (`WHERE NOW() BETWEEN start_time AND end_time`), whereas JSON requires app-level parsing.

**Alternative considered**: JSON column on `polls.schedule` — rejected because it complicates time-range querying and prevents DB-level indexing on schedule windows.

---

### D2 — Fair Rotation: `polls.last_shown_date` (per store, date-granular)

**Decision**: Track which poll was last displayed per store using a `poll_display_log` table with `(poll_id, store_id, shown_date)`. When multiple polls are eligible, exclude those shown today, then pick randomly from the remainder.

**Rationale**: Date-granular (not datetime) tracking means the "not shown today" rule resets cleanly at midnight. Per-store tracking means branch A and branch B rotate independently.

**Alternative considered**: Priority integer on each poll — rejected because it requires manual admin maintenance and doesn't self-balance.

---

### D3 — Impression Tracking: `poll_impressions` table

**Decision**: When a poll popup is shown to a visitor, record `(poll_id, visitor_id, shown_at)`. The API checks this table to exclude already-seen polls before returning the active poll.

**Rationale**: Reuses the existing `visitors` table FK. Impression is recorded at **popup display time**, not vote time — so even if the guest dismisses without voting, they won't see the same popup again.

**Important distinction**: The drawer tab does NOT consult `poll_impressions`. It always shows all currently active polls, indicating vote state (unvoted/voted/expired).

---

### D4 — Results: Computed on read, not stored

**Decision**: Result percentages are computed via `COUNT(*)` aggregation on `poll_votes` at query time, not pre-aggregated.

**Rationale**: Vote volumes are low (restaurant scale, not web-scale). Real-time accuracy on every vote response is more important than read performance. Can cache with a 30-second TTL if needed in future.

---

### D5 — API: Store-scoped endpoints under `/api/{store}/polls`

**Decision**: Poll endpoints are namespaced under the existing store slug pattern, consistent with the rest of the API.

## Risks / Trade-offs

- **Fingerprint collision** → Two different guests sharing a device see each poll once combined. Acceptable trade-off; same limitation exists for the existing analytics.
- **Clock drift on schedule windows** → If a guest's request arrives 1 second outside a window, they miss it. Mitigation: add a 1-minute grace buffer on `ends_at` comparison server-side.
- **Rotation fairness across branches** → `poll_display_log` is per-store, so two branches running the same poll rotate independently. This is the desired behaviour.

## Migration Plan

1. Run new migrations (`polls`, `poll_schedules`, `poll_options`, `poll_votes`, `poll_impressions`, `poll_display_log`)
2. Seed no data — system starts empty, admin creates polls
3. No rollback risk — purely additive, no existing tables modified
4. Frontend drawer tab is a new component; no existing components modified except drawer nav to add the tab

## Open Questions

- Should expired polls (past `ends_at`) remain visible in the drawer tab with results? *(Assumed: yes, for transparency)*
- Minimum vote threshold before results are revealed to the voter? *(Assumed: no threshold — results shown immediately)*
