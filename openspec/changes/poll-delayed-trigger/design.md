## Context

Currently, the `PollController@active` endpoint returns the active poll unconditionally if the user hasn't voted or seen it yet (depending on `show_once` flag). The frontend `PollPopup.jsx` fetches this immediately on load and shows it after 4 seconds. This leads to showing polls to users too early, potentially when they are just trying to browse the menu for the first time.

## Goals / Non-Goals

**Goals:**
- Delay poll visibility until the user's 2nd visit to the menu since the poll was published.
- Add a 10-second delay on the frontend before the poll pops up to ensure they are engaged.

**Non-Goals:**
- Tracking the exact duration of the *first* visit in the backend.
- Modifying the existing `Interaction` heartbeat logic.

## Decisions

- **Backend Visit Check**: We will use the existing `Visit` infrastructure. In `PollController@active`, we will count how many visits the `tracking_visitor_id` has that started *on or after* the poll's `created_at` date. If the count is `< 2`, we return `null`.
- **Frontend Timeout**: We will modify `PollPopup.jsx` to use a 10000ms timeout instead of 4000ms before setting `isVisible` to `true`.

## Risks / Trade-offs

- **Risk**: A user who visits twice but never stays for 10 seconds on the second visit will never see the poll.
- **Trade-off**: This is an intentional trade-off. We prefer to miss feedback from bounce users in order to avoid annoying them, focusing solely on engaged users.
