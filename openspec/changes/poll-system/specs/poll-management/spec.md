## ADDED Requirements

### Requirement: Admin can create a poll
An operator SHALL be able to create a poll by providing a title, question text, question type (single-choice, emoji-reaction, star-rating-1-5), and a list of answer options.

#### Scenario: Successful poll creation
- **WHEN** admin fills in title, question, type, and at least 2 options then saves
- **THEN** the poll is persisted with status `draft` and appears in the poll list

#### Scenario: Insufficient options
- **WHEN** admin saves a single-choice poll with fewer than 2 options
- **THEN** a validation error is shown and the poll is not saved

---

### Requirement: Admin can configure poll schedule
A poll SHALL support one or more schedule windows, each defining `starts_at` (datetime), `ends_at` (datetime), and optional `days_of_week` (array of 0-6). When no days are specified the schedule applies every day.

#### Scenario: Poll activates within scheduled window
- **WHEN** the current server time falls within a schedule's `starts_at`–`ends_at` range and the current day matches `days_of_week` (or days_of_week is empty)
- **THEN** the poll is considered active for that store

#### Scenario: Poll inactive outside schedule
- **WHEN** the current time is outside all schedule windows
- **THEN** the poll is NOT returned by the active-poll API

---

### Requirement: Admin can scope a poll to a branch
A poll SHALL support an optional `store_id`. When set, the poll is only active for that store. When null, the poll is active for all stores.

#### Scenario: Branch-scoped poll excluded from other branches
- **WHEN** a poll has `store_id = 2` and a visitor from `store_id = 1` requests the active poll
- **THEN** that poll is NOT included in the response

---

### Requirement: Admin can toggle "show once per visitor"
A poll SHALL have a boolean `show_once` flag. When enabled, the automatic popup for that poll is shown at most once per unique visitor fingerprint.

#### Scenario: Show-once poll not repeated as popup
- **WHEN** `show_once = true` and `poll_impressions` already contains a row for this visitor + poll
- **THEN** the poll is excluded from the popup API response but remains accessible via the drawer tab

---

### Requirement: Admin can activate or archive a poll
A poll SHALL have a status field (`draft`, `active`, `archived`). Only `active` polls participate in scheduling resolution.

#### Scenario: Draft poll not served
- **WHEN** a poll has status `draft`
- **THEN** it is not returned by the active-poll API regardless of schedule

#### Scenario: Archived poll visible in admin history
- **WHEN** a poll is archived
- **THEN** it appears in the admin "Arşiv" tab with its final vote totals
