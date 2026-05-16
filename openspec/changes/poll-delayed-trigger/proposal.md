## Why

We want to show polls to engaged users at the right time. Bombarding users with a poll on their very first visit or immediately upon opening the page can lead to low engagement and annoyance. By showing the poll on the user's 2nd visit (since the poll was published) and waiting 10 seconds before displaying it, we ensure that the user has had time to interact with the menu and is more likely to provide meaningful feedback.

## What Changes

- **Backend (`PollController@active`)**: The logic will change to count the number of `Visit` records for the user that occurred *after* the poll's start date (`schedules->min('starts_at')` or `created_at`). The poll is only returned if this count is >= 2.
- **Frontend (`PollPopup.jsx`)**: The timeout before showing the poll popup will be increased from 4 seconds to 10 seconds.

## Capabilities

### New Capabilities
- `poll-delayed-trigger`: Delays poll display to the 10th second of the user's 2nd visit since the poll became active.

### Modified Capabilities

## Impact

- `app/Http/Controllers/PollController.php`
- `resources/js/Components/Polls/PollPopup.jsx`
