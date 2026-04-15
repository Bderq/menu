# Tasks: Anonymous Product Voting

## Phase 1: Database & Model (Task 1)
- [ ] 1.1 Create migration for `votes` table with unique index on `visitor_id` and `product_id`.
- [ ] 1.2 Create `Vote` model and define relationships.
- [ ] 1.3 Add `votes()` relationship to `Visitor` and `Product` models.

## Phase 2: API & Logic (Task 2)
- [ ] 2.1 Implement `toggleVote` method in `TrackingController`.
- [ ] 2.2 Register route `POST /tracking/vote`.
- [ ] 2.3 Ensure `TrackVisitor` middleware is applied to the route.

## Phase 3: Frontend Integration (Task 3)
- [ ] 3.1 Update `MenuController` to pass `likedProductIds` to the Inertia `Index` page.
- [ ] 3.2 Update `useTracking` hook to include a `toggleVote` function.
- [ ] 3.3 Add heart icon/button to `ProductCard` and handle click events.
- [ ] 3.4 Implement optimistic UI updates for the "like" state.

## Phase 4: Analytics (Task 4)
- [ ] 4.1 Update `AnalyticsDashboard` to show top-liked products.
- [ ] 4.2 Add a "Total Votes" counter to `AnalyticsStats`.

## Phase 5: Verification (Task 5)
- [ ] 5.1 Manual test: Like a product, refresh page, verify it stays liked.
- [ ] 5.2 Manual test: Like in normal tab, then in incognito (verify fingerprint recovery links it).
- [ ] 5.3 Run `php artisan test` (if applicable).
