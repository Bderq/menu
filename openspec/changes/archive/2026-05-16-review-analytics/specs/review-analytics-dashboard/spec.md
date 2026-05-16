## ADDED Requirements

### Requirement: Admin Dashboard Widget
The Filament admin panel MUST display metrics for the Google Review Prompt funnel.

#### Scenario: Widget Display
- **WHEN** an admin views the dashboard
- **THEN** a `GoogleReviewStatsWidget` is rendered containing statistic cards.

#### Scenario: Stat Calculation
- **WHEN** the widget calculates statistics for a given store
- **THEN** it correctly queries `google_review_interactions` to display:
  - Total Shows (`status = showed` count)
  - Accepted Rate (`accepted` / `showed` percentage)
  - Google Redirect Rate (`google_redirected = true` / `accepted` percentage)
  - Rejected Rate (`rejected` / `showed` percentage)
  - Feedback Completion Rate (`feedback_submitted = true` / `rejected` percentage)
  - Dismissed Rate (`dismissed` / `showed` percentage)
