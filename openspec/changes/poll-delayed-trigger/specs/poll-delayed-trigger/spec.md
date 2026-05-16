## ADDED Requirements

### Requirement: Delayed Poll Display (Visit Count and 10s Timer)
The system must not display the poll to a visitor on their first visit since the poll was activated, and must wait 10 seconds before displaying it on the second visit.

#### Scenario: 1st Visit After Poll Activation
- **WHEN** a visitor loads the page and it is their first recorded visit since the poll was created.
- **THEN** the active poll API must return null, and no poll is shown.

#### Scenario: 2nd Visit After Poll Activation
- **WHEN** a visitor loads the page for the 2nd time since the poll was created.
- **THEN** the active poll API returns the poll details.
- **AND THEN** the frontend waits exactly 10 seconds before rendering the poll popup on screen.
