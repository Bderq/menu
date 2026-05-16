## 1. Backend Modifications

- [x] 1.1 In `app/Http/Controllers/PollController.php`, update the `active` method to calculate the poll's activation date (`schedules->min('starts_at')` or `created_at`).
- [x] 1.2 In `app/Http/Controllers/PollController.php`, update the `active` method to count `Visit` records for the `$visitorId` where `started_at` is `>=` the poll's activation date.
- [x] 1.3 Return `null` if the calculated visit count is less than 2.

## 2. Frontend Modifications

- [x] 2.1 In `resources/js/Components/Polls/PollPopup.jsx`, locate the `setTimeout` that sets `isVisible` to `true`.
- [x] 2.2 Change the delay value from `4000` to `10000` milliseconds.
