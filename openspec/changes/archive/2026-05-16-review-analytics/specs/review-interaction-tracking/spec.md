## ADDED Requirements

### Requirement: Database Storage
The system MUST store atomic interactions for the Google Review Prompt.

#### Scenario: Database Schema
- **WHEN** the `google_review_interactions` table is queried
- **THEN** it must have `visitor_id`, `store_id`, `status` (string), `google_redirected` (boolean), `feedback_submitted` (boolean), `guest_message_id` (foreign key), `showed_at` (timestamp), and `responded_at` (timestamp).

### Requirement: API Endpoints
The backend MUST expose endpoints to track the interaction funnel.

#### Scenario: Logging Initial Show
- **WHEN** a POST request is made to `/api/{store_slug}/review-interaction`
- **THEN** a new interaction record is created with `status: showed` and the current timestamp for `showed_at`, returning the interaction `id`.

#### Scenario: Updating Interaction Status
- **WHEN** a PATCH request is made to `/api/{store_slug}/review-interaction/{id}` with a `status` payload (`accepted`, `rejected`, `dismissed`)
- **THEN** the interaction record is updated with the new `status` and the current timestamp for `responded_at`.

#### Scenario: Logging Google Redirect
- **WHEN** a POST request is made to `/api/{store_slug}/review-interaction/{id}/google-clicked`
- **THEN** the interaction record is updated with `google_redirected: true`.

### Requirement: Frontend Tracking
The frontend `GoogleReviewPopup` MUST interact with the new API endpoints without blocking the UI.

#### Scenario: Tracking Show Event
- **WHEN** the popup becomes visible
- **THEN** an async POST request is sent to create the interaction, and the returned `interaction_id` is stored in the component's state.

#### Scenario: Tracking User Action
- **WHEN** the user clicks "Yes", "No", or dismisses the popup
- **THEN** an async PATCH request is sent to update the interaction status using the stored `interaction_id`.

#### Scenario: Tracking Google Redirect
- **WHEN** the user clicks the "Değerlendir" button to go to Google
- **THEN** an async POST request is sent to track the redirect using the stored `interaction_id` before opening the new tab.
