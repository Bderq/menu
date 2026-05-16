## Context

The current Google Review Prompt uses `localStorage` on the client side to track if a user has seen the popup and to ensure it only shows once. However, this does not allow us to track the interaction funnel on the server side to determine how successful the prompt is in converting users to leave Google reviews or provide internal feedback.

## Goals / Non-Goals

**Goals:**
- Track each step of the Google Review Prompt interaction funnel: showed, accepted, rejected, dismissed.
- Track secondary actions: redirected to Google, submitted internal feedback.
- Provide a summary dashboard widget in Filament for the store admin to view these metrics.
- Keep the interaction tracking lightweight to avoid impacting user experience.

**Non-Goals:**
- Do not track individual users across sessions; anonymous tracking tied to `visitor_id` is sufficient.
- Do not change the logic for *when* the prompt is shown (i.e., we are not altering the 'distinct business day' rule).

## Decisions

- **Database Table:** Create `google_review_interactions` to store atomic interactions. This allows for detailed funnel analysis (e.g., how many who clicked 'No' actually submitted the 'Ses Ver' form).
- **Status Field:** Use a string column for `status` (`showed`, `accepted`, `rejected`, `dismissed`) to capture the primary action.
- **Secondary Flags:** Use boolean columns (`google_redirected`, `feedback_submitted`) and an optional foreign key `guest_message_id` to capture actions that happen *after* the initial response.
- **Frontend Tracking:** Update the existing React `GoogleReviewPopup` component to make `fetch` or `axios` calls in the background when the state changes.
- **Feedback Integration:** Pass the interaction ID to the "Ses Ver" drawer so that if the user submits feedback, the backend can update the `google_review_interactions` row.

## Risks / Trade-offs

- **Risk:** Unreliable network could lead to missed tracking events.
- **Mitigation:** Use non-blocking async requests. We prefer losing a tracking event over blocking the UI.
- **Trade-off:** We are introducing state to the backend for something that was previously purely frontend-driven, increasing database writes, but the value of the analytics justifies the load.
