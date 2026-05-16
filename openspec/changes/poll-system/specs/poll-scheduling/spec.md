## ADDED Requirements

### Requirement: System resolves active polls for a store at a given time
The system SHALL evaluate all `active` polls and their schedules to determine which polls are currently active for a given `store_id` and the current server time.

#### Scenario: Single active poll returned
- **WHEN** exactly one poll's schedule matches the current time and store
- **THEN** that poll is returned as the active poll

#### Scenario: No active polls
- **WHEN** no poll schedule matches the current time and store
- **THEN** the API returns an empty response (no poll shown)

---

### Requirement: Fair rotation resolves overlapping polls
When multiple polls are simultaneously active for the same store, the system SHALL apply fair rotation: prefer polls not displayed today (recorded in `poll_display_log`); among those, select randomly. If all overlapping polls were displayed today, select randomly among all of them.

#### Scenario: Two polls overlap — one shown today
- **WHEN** polls A and B are both active and poll A has a `poll_display_log` entry for today's date for this store
- **THEN** poll B is selected

#### Scenario: Two polls overlap — neither shown today
- **WHEN** polls A and B are both active and neither has a log entry for today
- **THEN** one is selected at random

#### Scenario: Two polls overlap — both shown today
- **WHEN** polls A and B are both active and both have log entries for today
- **THEN** one is selected at random (fairness constraint relaxed)

---

### Requirement: Display log is updated when a poll is served
When a poll is selected and served to any visitor, the system SHALL upsert a row in `poll_display_log` for `(poll_id, store_id, shown_date = today)`.

#### Scenario: Log entry created on first serve
- **WHEN** a poll is selected by the rotation algorithm and included in the API response
- **THEN** a `poll_display_log` row is created (or updated if already exists for today)
