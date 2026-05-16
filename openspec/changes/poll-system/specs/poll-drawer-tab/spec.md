## ADDED Requirements

### Requirement: Menu drawer includes an Anket tab
The frontend drawer navigation SHALL include an **Anket** tab alongside existing tabs. The tab displays all currently active polls for the current store.

#### Scenario: Anket tab visible in drawer
- **WHEN** guest opens the bottom drawer
- **THEN** an "Anket" tab is visible in the navigation

#### Scenario: No active polls — empty state shown
- **WHEN** the Anket tab is opened and no polls are active
- **THEN** an empty state message is displayed (e.g., "Şu an aktif anket yok")

---

### Requirement: Unvoted polls show vote options in drawer
For each active poll where the visitor has not voted, the drawer SHALL render the question and answer options as interactive buttons.

#### Scenario: Visitor votes from drawer
- **WHEN** visitor taps an option in the drawer
- **THEN** the vote is submitted and the option buttons are replaced with animated result bars

---

### Requirement: Voted polls show results in drawer
For each active poll where the visitor has already voted, the drawer SHALL display the result bars with percentages and highlight the visitor's chosen option.

#### Scenario: Previously voted poll shows results immediately
- **WHEN** visitor opens the Anket tab after having voted
- **THEN** result bars are shown immediately with the visitor's answer highlighted

---

### Requirement: Automatic popup shows active poll on menu load
When the menu page loads, the system SHALL check for an eligible popup poll (via `GET /api/{store}/polls/active`) and display a modal/floating card if one exists.

#### Scenario: Popup shown on first eligible visit
- **WHEN** visitor opens the menu and an eligible poll exists
- **THEN** a floating poll card appears after the configured trigger delay

#### Scenario: Popup not repeated after impression
- **WHEN** `show_once = true` and the visitor has already seen the popup
- **THEN** no popup is shown on subsequent visits

#### Scenario: Voting from popup shows results inline
- **WHEN** visitor submits a vote via the popup
- **THEN** the option buttons animate out and result percentage bars animate in within the same popup card
