## 1. Database Migrations

- [x] 1.1 Create `polls` migration: `id, store_id (nullable FK), title, question, type (enum: single_choice|emoji_reaction|star_rating), status (enum: draft|active|archived), show_once (bool), timestamps`
- [x] 1.2 Create `poll_schedules` migration: `id, poll_id (FK), starts_at (datetime), ends_at (datetime), days_of_week (JSON nullable), timestamps`
- [x] 1.3 Create `poll_options` migration: `id, poll_id (FK), text, emoji (nullable), sort_order, timestamps`
- [x] 1.4 Create `poll_votes` migration: `id, poll_id (FK), poll_option_id (FK), visitor_id (FK), store_id (FK), timestamps` — unique on `(poll_id, visitor_id)`
- [x] 1.5 Create `poll_impressions` migration: `id, poll_id (FK), visitor_id (FK), shown_at (timestamp)` — unique on `(poll_id, visitor_id)`
- [x] 1.6 Create `poll_display_log` migration: `id, poll_id (FK), store_id (FK), shown_date (date)` — unique on `(poll_id, store_id, shown_date)`
- [x] 1.7 Run migrations and verify all tables created

## 2. Models & Relationships

- [x] 2.1 Create `Poll` model with relationships: `hasMany(PollSchedule)`, `hasMany(PollOption)`, `hasMany(PollVote)`, `hasMany(PollImpression)`, `belongsTo(Store)` (nullable)
- [x] 2.2 Create `PollSchedule`, `PollOption`, `PollVote`, `PollImpression`, `PollDisplayLog` models
- [x] 2.3 Add `isActiveNow(storeId)` scope on `Poll` that joins schedules and checks current datetime + days_of_week
- [x] 2.4 Add `PollSchedulingService` class implementing fair rotation: resolves active polls for a store, applies display log, returns selected poll

## 3. Admin Panel — Poll Management

- [x] 3.1 Create `PollResource` (Filament) with list, create, edit pages — columns: title, type badge, status badge, schedule summary, vote count
- [x] 3.2 Add `RepeaterField` for poll options in create/edit form (text + optional emoji, sortable)
- [x] 3.3 Add `RepeaterField` for schedules: date range picker + time range + days_of_week checkboxes + store select
- [x] 3.4 Add `show_once` toggle and `status` select to the form
- [x] 3.5 Add status tabs (Active / Draft / Archived) to the poll list

## 4. Admin Panel — Poll Results Dashboard

- [x] 4.1 Create `PollDashboard` Filament page at `/admin/polls/results`
- [x] 4.2 Add poll selector and branch filter to dashboard header
- [x] 4.3 Create result widget showing per-option vote counts and percentages with progress bars
- [x] 4.4 Add navigation link in Filament sidebar

## 5. Backend API

- [x] 5.1 Create `PollController` with `active(store)` action — runs `PollSchedulingService`, applies impression filter, records impression + display log, returns poll JSON
- [x] 5.2 Create `vote(store, poll)` action — validates `option_id`, checks duplicate via `poll_votes` unique constraint, stores vote, returns result percentages
- [x] 5.3 Create `index(store)` action — returns all active polls for the store with per-visitor vote state (`unvoted` | `voted` + results)
- [x] 5.4 Register routes: `GET /api/{store}/polls/active`, `POST /api/{store}/polls/{poll}/vote`, `GET /api/{store}/polls`
- [x] 5.5 Apply rate limiting on vote endpoint (max 10/minute per IP)

## 6. Frontend — Poll Popup

- [x] 6.1 Create `PollPopup` React component — floating card with question, option buttons (single-choice / emoji / star variants)
- [x] 6.2 On menu page mount, fetch `GET /api/{store}/polls/active` and show popup if response non-empty (after configured delay — default 0s)
- [x] 6.3 On vote submission, animate option buttons out and render `PollResults` component in their place
- [x] 6.4 Create `PollResults` component — animated progress bars per option, highlight voted option, show total vote count

## 7. Frontend — Drawer Anket Tab

- [x] 7.1 Add "Anket" tab to existing drawer navigation component
- [x] 7.2 Create `PollDrawerTab` component — fetches `GET /api/{store}/polls` on tab open, renders list of polls
- [x] 7.3 Render `PollCard` for each poll: unvoted state shows options; voted state shows `PollResults` with highlighted answer
- [x] 7.4 Submitting a vote from the drawer updates that poll's card to results view inline

## 8. Verification & Polish

- [x] 8.1 Test: create a poll with a schedule that ends yesterday — verify it does not appear via API
- [x] 8.2 Test: vote twice on same poll with same visitor fingerprint — verify 409 on second attempt
- [x] 8.3 Test: two overlapping polls — verify rotation logic (show B after A was shown today)
- [x] 8.4 Test: `show_once = true` poll — verify popup suppressed on second visit, still visible in drawer tab
- [x] 8.5 Manual: vote from popup → results appear inline; open drawer → same poll shows results with highlight
- [x] 8.6 Add SEO Meta Tags to StreetLayout
- [x] 8.7 Successful Production Build
