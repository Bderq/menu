## ADDED Requirements

### Requirement: Admin can view a dedicated poll results page
The system SHALL provide a Filament page at `/admin/polls` listing all polls (active, draft, archived) with vote totals and per-option breakdowns.

#### Scenario: Results page shows vote counts
- **WHEN** admin navigates to `/admin/polls` and opens a poll
- **THEN** each option is displayed with its vote count and percentage

---

### Requirement: Results can be filtered by branch
The admin results view SHALL allow filtering by `store_id` to see per-branch vote distributions for a given poll.

#### Scenario: Branch filter applied
- **WHEN** admin selects a specific branch in the filter
- **THEN** only votes cast by visitors of that branch are included in the totals

---

### Requirement: Poll list distinguishes active, draft, and archived states
The admin poll list SHALL visually separate polls by status with colour-coded badges and grouped tabs (Active / Draft / Archived).

#### Scenario: Active polls highlighted
- **WHEN** a poll is currently within a valid schedule window and status is `active`
- **THEN** it is displayed with a green "Aktif" badge in the list

#### Scenario: Archived polls accessible with final totals
- **WHEN** a poll is archived
- **THEN** it appears in the Archived tab and its vote totals remain readable
