## ADDED Requirements

### Requirement: Admin Configuration for Google Review
Store administrators must be able to specify their Google Business review URL and custom prompt question.

#### Scenario: Admin configures google review settings
- **WHEN** Admin edits a Store via the Filament panel
- **THEN** The admin can input a `google_review_url` and `google_review_question`.

### Requirement: Triggering Google Review Prompt
The system must show the Google Review Prompt to returning visitors after 10 seconds of dwell time on the menu.

#### Scenario: First-time visitor views menu
- **WHEN** A visitor with 1 visit views the menu
- **THEN** The Google Review Prompt is NOT shown.

#### Scenario: Returning visitor views menu
- **WHEN** A visitor with 2 or more visits views the menu, and `google_review_url` and `google_review_question` are configured, and the prompt hasn't been shown before
- **THEN** The Google Review Prompt is shown after 10 seconds.

### Requirement: Responding to Google Review Prompt
The system must handle the visitor's response to the prompt (Yes/No) and direct them accordingly.

#### Scenario: Visitor clicks Yes
- **WHEN** The visitor clicks "Yes" or equivalent on the prompt
- **THEN** A new browser tab opens pointing to the store's `google_review_url`, and the prompt is marked as seen in `localStorage`.

#### Scenario: Visitor clicks No
- **WHEN** The visitor clicks "No" or equivalent on the prompt
- **THEN** The prompt is marked as seen in `localStorage`, the prompt closes, and the "Ses Ver" (Feedback) drawer is opened.

#### Scenario: Visitor dismisses the prompt
- **WHEN** The visitor clicks the close (X) button on the prompt
- **THEN** The prompt is marked as seen in `localStorage` and closes without further action.
