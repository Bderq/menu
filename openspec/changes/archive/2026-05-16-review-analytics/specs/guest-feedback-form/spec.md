## ADDED Requirements

### Requirement: Link Feedback to Review Interaction
The guest feedback submission MUST be able to link back to the Google Review Prompt interaction that initiated it.

#### Scenario: Submitting Feedback from Review Prompt
- **WHEN** the "Ses Ver" form is submitted with a `review_interaction_id` in the request
- **THEN** the corresponding `google_review_interactions` record is updated with `feedback_submitted: true` and the newly created `guest_message_id`.

#### Scenario: Passing Interaction ID from Popup to Drawer
- **WHEN** a user clicks "No" on the Google Review Prompt
- **THEN** the `interaction_id` is passed to the `MenuInteractionDrawer` and included in the feedback form payload upon submission.
