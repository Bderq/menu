## ADDED Requirements

### Requirement: Independent Models for Diet and Allergens
The system SHALL support `Allergen` and `DietType` as independent database models with `name`, `icon`, and `color` properties.

#### Scenario: Creating an Allergen
- **WHEN** an admin inserts a new Allergen from the Filament interface
- **THEN** a new record is created in the `allergens` table

#### Scenario: Creating a Diet Type
- **WHEN** an admin inserts a new Diet Type from the Filament interface
- **THEN** a new record is created in the `diet_types` table

### Requirement: Master Product Associations
The system SHALL allow `Product` models to have multiple `Allergen` and `DietType` associations via many-to-many pivot tables.

#### Scenario: Attaching Tags to Product
- **WHEN** a Master Product is saved
- **THEN** selected allergens and diet types are stored in the respectively defined pivot tables without affecting specific store configurations

### Requirement: Filament Resource Management
The system SHALL provide Admin Panel pages for managing Diet Types and Allergens globally.

#### Scenario: Listing Resources
- **WHEN** an admin navigates to the Diet Types or Allergens menu
- **THEN** they see an index table of all existing rows

### Requirement: Product Form Selector
The Filament Product form SHALL expose multi-select UI elements for Diet Types and Allergens.

#### Scenario: Selecting Tags
- **WHEN** creating or editing a Product
- **THEN** the admin can search for and select multiple allergens and diet types visually
