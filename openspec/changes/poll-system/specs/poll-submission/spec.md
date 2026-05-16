## ADDED Requirements

### Requirement: Guest can fetch the active poll for a store
The system SHALL expose `GET /api/{store}/polls/active` which returns the currently active poll for the visitor (if any). The `visitor_id` is resolved from the request fingerprint header.

Exclusions applied before returning:
1. Polls where `show_once = true` AND a `poll_impressions` row exists for this visitor
2. Polls not currently active per schedule

#### Scenario: Eligible poll returned
- **WHEN** an active poll exists and the visitor has not seen it (or show_once is false)
- **THEN** the response includes poll id, question, type, and options

#### Scenario: Already-seen poll excluded from popup
- **WHEN** `show_once = true` and the visitor already has an impression record for that poll
- **THEN** the response is empty (no popup triggered)

#### Scenario: Impression recorded on response
- **WHEN** a poll is included in the response
- **THEN** a `poll_impressions` row is created for `(poll_id, visitor_id, shown_at = now())`

---

### Requirement: Guest can submit a vote
The system SHALL expose `POST /api/{store}/polls/{poll}/vote` accepting `option_id`. A visitor MAY only vote once per poll.

#### Scenario: Successful vote
- **WHEN** visitor submits a valid `option_id` for a poll they have not yet voted on
- **THEN** the vote is stored and the response includes current result percentages for all options

#### Scenario: Duplicate vote rejected
- **WHEN** visitor submits a vote for a poll they have already voted on
- **THEN** a 409 response is returned; the existing vote is not changed

#### Scenario: Results returned immediately after vote
- **WHEN** a vote is accepted
- **THEN** the response body contains `{ options: [{ id, text, votes, percentage }] }`

---

### Requirement: Guest can fetch all active polls for the drawer tab
The system SHALL expose `GET /api/{store}/polls` returning all currently active polls for the store, regardless of impression history. Each poll includes the visitor's vote state (`unvoted` | `voted`).

#### Scenario: Voted poll shows results
- **WHEN** visitor has a vote record for a poll
- **THEN** the poll is returned with `state: "voted"` and result percentages included

#### Scenario: Unvoted poll shows options only
- **WHEN** visitor has not voted on a poll
- **THEN** the poll is returned with `state: "unvoted"` and options listed without percentages
