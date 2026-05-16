## 1. Database and Models

- [x] 1.1 Create migration `create_google_review_interactions_table` with `visitor_id`, `store_id`, `status` (string), `google_redirected` (boolean), `feedback_submitted` (boolean), `guest_message_id` (foreign key nullable), `showed_at` (timestamp), and `responded_at` (timestamp).
- [x] 1.2 Run `php artisan migrate` to ensure the table is created.
- [x] 1.3 Create Eloquent model `GoogleReviewInteraction` with `$fillable` properties and relationships to `Visitor`, `Store`, and `GuestMessage`.

## 2. Backend API and Controllers

- [x] 2.1 Create `GoogleReviewInteractionController` with `store()` method to handle initial 'showed' event.
- [x] 2.2 Add `update()` method to `GoogleReviewInteractionController` to handle status changes ('accepted', 'rejected', 'dismissed').
- [x] 2.3 Add `googleClicked()` method to `GoogleReviewInteractionController` to handle Google redirection tracking.
- [x] 2.4 Register the three new API routes in `routes/web.php` under the `api/{store_slug}/review-interaction` prefix.
- [x] 2.5 Update `GuestMessageController::store()` to optionally accept `review_interaction_id`, and if present, update the corresponding `GoogleReviewInteraction` record with `feedback_submitted = true` and `guest_message_id`.

## 3. Frontend Integration

- [x] 3.1 Update `GoogleReviewPopup.jsx` to make an async POST request to create an interaction when `isVisible` becomes true, and store the returned `interaction_id`.
- [x] 3.2 Update `handleYes()` in `GoogleReviewPopup.jsx` to send an async PATCH request setting status to 'accepted'.
- [x] 3.3 Update `handleGoogleRedirect()` in `GoogleReviewPopup.jsx` to send an async POST request to the `google-clicked` endpoint before opening the new tab.
- [x] 3.4 Update `handleNo()` and `handleDismiss()` in `GoogleReviewPopup.jsx` to send async PATCH requests setting status to 'rejected' or 'dismissed', respectively.
- [x] 3.5 Pass the `interaction_id` from `GoogleReviewPopup` down to the `MenuInteractionDrawer` and into the feedback form submission payload so it links to the `GuestMessage`.

## 4. Admin Dashboard

- [x] 4.1 Create a Filament widget `GoogleReviewStatsWidget`.
- [x] 4.2 Implement query logic in the widget to calculate total shows, accepted rate, rejected rate, Google redirect rate, and feedback submission rate based on the `google_review_interactions` table.
- [x] 4.3 Add the widget to the Store dashboard view in Filament.
