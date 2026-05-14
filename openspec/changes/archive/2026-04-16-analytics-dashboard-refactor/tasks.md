## 1. Veritabanı ve Şema (Database & Schema)

- [x] 1.1 Create migration to add nullable `store_id` to `visits` table (foreign key referencing stores).
- [x] 1.2 Create migration to add multi-column index `(created_at, interactable_type, interactable_id)` to `interactions` table.

## 2. Backend Logic (Middleware & Controllers)

- [x] 2.1 Update `TrackVisitor` middleware (or `MenuController` / API logic depending on the request source) to resolve the active Store from the URL or headers, and assign its ID (`store_id`) to the `Visit` model.
- [x] 2.2 Update `TrackingController@hit` validation to return HTTP 400 Bad Request if the `model` payload is not in the allowed list (`Product` or `Category`), preventing `null` inserts.
- [x] 2.3 Add Bot/Crawler detection logic to `TrackVisitor` middleware to bail early and prevent phantom visits.

## 3. Analitik Paneli Düzeltmeleri (Filament)

- [x] 3.1 Update `AnalyticsStats.php` widget.
- [x] 3.2 Update `TopInteractionsTable.php`: Join with visits and stores to group interactions by specific store context.
- [x] 3.3 Update `TopLikesTable.php`.

## 4. Client Side & UX Fixes

- [x] 4.1 Update `useTracking.js` to handle `visibilitychange` events and pause heartbeat tracking when the document is hidden.
