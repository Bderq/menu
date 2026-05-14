## 1. Veritabanı (Database)

- [x] 1.1 Create migration to add `referer_host` (string, nullable) and `utm_source` (string, nullable) to `visits` table.

## 2. Backend & Logic

- [x] 2.1 Update `TrackVisitor` middleware to capture `Referer` header and clean it (extract host).
- [x] 2.2 Update `TrackVisitor` middleware to capture `utm_source` from query parameters.

## 3. UI & Analytics (Filament)

- [x] 3.1 Create `VisitorSourcesTable` or `VisitorSourcesWidget` to list the most frequent origins for the last 24 hours.
- [x] 3.2 Register the new widget in `AnalyticsDashboard.php`.
