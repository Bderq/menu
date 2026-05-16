## Why

Currently, the Google Review Prompt feature only uses `localStorage` on the client side to track if a user has seen the popup and to ensure it only shows once. It leaves no trace on the server, making it impossible to analyze the effectiveness of the feature. We need to measure how many people saw the prompt, how many clicked "Yes" (and went to Google), how many clicked "No" (and provided feedback via "Ses Ver"), and how many simply dismissed it. This interaction funnel analytics is crucial to understanding our Google Review conversion rate.

## What Changes

We will introduce an interaction funnel tracking mechanism specifically for the Google Review Prompt. Instead of relying solely on frontend state, every user interaction with the popup (showed, accepted, rejected, dismissed) will log an entry to a new database table (`google_review_interactions`). We will add backend API endpoints to handle these interactions, update the React frontend to push interaction data without blocking the UI, link feedback form submissions to these interactions, and display the aggregated metrics in a Filament admin dashboard widget.

## Capabilities

### New Capabilities
- `review-interaction-tracking`: Server-side logging of Google Review Prompt funnel steps (show, yes, no, dismiss).
- `review-analytics-dashboard`: Filament admin widget to display interaction counts and conversion rates.

### Modified Capabilities
- `guest-feedback-form`: Modifying the "Ses Ver" feedback submission to optionally accept and link a `review_interaction_id` to track feedback originating from rejected review prompts.

## Impact

- **Database:** A new `google_review_interactions` table will be created.
- **Backend APIs:** New routes and a controller (`GoogleReviewInteractionController`) will be added to handle incoming interactions.
- **Frontend Components:** `GoogleReviewPopup.jsx` will be updated to make async POST/PATCH requests on interaction. `Index.jsx` and `MenuInteractionDrawer.jsx` will be slightly updated to pass interaction context to the Ses Ver form.
- **Admin Panel:** A new stats widget (`GoogleReviewStatsWidget`) will be added.
