## 1. Database & Backend Configuration

- [x] 1.1 Create migration to add `google_review_url` (string, nullable) and `google_review_question` (string, nullable) to `stores` table and run migration.
- [x] 1.2 Update `MenuController::index()` to query the number of visits for the current `$visitorId` to the current `$store->id`, and pass `visitCount` to Inertia render.

## 2. Admin Panel Configuration

- [x] 2.1 Update `StoreResource.php` form schema to include a new section "Google Business" containing a URL input for `google_review_url` and a Textarea input for `google_review_question`.

## 3. Frontend Implementation

- [x] 3.1 Create `resources/js/Components/GoogleReviewPopup.jsx` with brutalist design (matching PollPopup). It should accept `storeSlug`, `visitCount`, `googleReviewUrl`, `googleReviewQuestion`, and `onOpenSesVer` props.
- [x] 3.2 Implement logic in `GoogleReviewPopup` to only show after 10 seconds if `visitCount >= 2`, URL and Question are present, and `localStorage.getItem('google_review_seen_' + storeSlug)` is not set.
- [x] 3.3 Implement "Yes" click action to open `googleReviewUrl` in a new tab, mark as seen in `localStorage`, and hide prompt.
- [x] 3.4 Implement "No" click action to trigger `onOpenSesVer()`, mark as seen in `localStorage`, and hide prompt.
- [x] 3.5 Update `resources/js/Pages/Menu/Index.jsx` to import and render `<GoogleReviewPopup>` alongside `PollPopup`, passing the necessary props (`visitCount`, `store.google_review_url`, `store.google_review_question`) and wiring `onOpenSesVer` to open the drawer and switch to the Feedback (Ses Ver) tab.
