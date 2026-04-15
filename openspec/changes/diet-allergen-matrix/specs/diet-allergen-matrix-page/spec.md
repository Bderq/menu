## ADDED Requirements

### Requirement: Diet Allergen Matrix Page Exists
The system SHALL provide a dedicated Filament admin page accessible from the "Ürün Yönetimi" navigation group for bulk management of diet types and allergens.

#### Scenario: Admin navigates to the page
- **WHEN** an admin clicks "Diyet & Alerjen Matrix" in the sidebar
- **THEN** the page loads displaying a category dropdown and a matrix table

### Requirement: Category Filtering
The system SHALL allow filtering the product rows by category.

#### Scenario: Admin changes the category
- **WHEN** an admin selects a different category from the dropdown
- **THEN** the matrix rows update to show only products in that category without a full page reload

### Requirement: Matrix Toggle
The system SHALL let admins attach or detach diet types and allergens to products by clicking cells.

#### Scenario: Admin checks a cell
- **WHEN** an admin clicks an unchecked cell for a product + tag combination
- **THEN** the pivot record is immediately created in the database and the cell shows as checked

#### Scenario: Admin unchecks a cell
- **WHEN** an admin clicks a checked cell
- **THEN** the pivot record is immediately deleted and the cell shows as unchecked

### Requirement: Readable Column Headers
The system SHALL render column headers with background color from the tag's `color` field and contrasted text color to ensure readability.

#### Scenario: Dark background column header
- **WHEN** a tag's `color` has relative luminance < 0.4
- **THEN** the header text renders in white (#FFFFFF)

#### Scenario: Light background column header
- **WHEN** a tag's `color` has relative luminance >= 0.4
- **THEN** the header text renders in black (#000000)

### Requirement: Visual Section Divider
The system SHALL display a visual divider between the diet type columns and the allergen columns.

#### Scenario: Divider rendered
- **WHEN** the matrix is displayed
- **THEN** a thick vertical border separates the last diet type column from the first allergen column
