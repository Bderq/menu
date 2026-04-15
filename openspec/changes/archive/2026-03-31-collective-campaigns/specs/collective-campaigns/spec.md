## ADDED Requirements

### Requirement: Collective Campaign Type
The system SHALL support a `COLLECTIVE` type in the `CampaignType` enum. Campaigns of this type SHALL be able to define multiple quantity-to-price tiers stored as JSON arrays.

#### Scenario: Admin creates a collective campaign
- **WHEN** the admin selects "Collective" as the campaign type in the Filament dashboard
- **THEN** a Repeater field SHALL appear allowing the admin to input pairs of quantity and price (e.g., Qty: 4, Price: 580).

#### Scenario: Admin saves collective tiers
- **WHEN** the form is saved
- **THEN** the array of tiers SHALL be persisted into the `tiers` JSON column in the `campaigns` table.

### Requirement: Service Application of Collective Tiers
The `CampaignService` SHALL detect `COLLECTIVE` campaigns and inject their tier data into the serialized product matrix.

#### Scenario: Frontend requests structured menu with collective campaigns
- **WHEN** a product falls under a `COLLECTIVE` campaign umbrella
- **THEN** its serialised data payload SHALL include a `collective_tiers` array, and its `campaign_type` SHALL equal `collective`.

### Requirement: Collective Frontend Representation
The frontend application SHALL distinctively render `COLLECTIVE` campaigns to show matrix pricing instead of a single discounted price.

#### Scenario: Customer views collective campaign card
- **WHEN** the user explores a "Collective Campaign" in the frontend gallery or clicks to open its details drawer
- **THEN** the interface SHALL display the available quantity options alongside their corresponding total and/or per-unit prices in a list or table layout, rather than crossing out a single price.
