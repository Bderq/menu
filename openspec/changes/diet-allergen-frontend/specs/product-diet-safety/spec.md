# Spec: Product Diet and Safety Display

## Capability: `product-diet-safety`

### Requirement 1: Data Exposure (Backend)
- **Description**: The `Product` model data returned to the frontend must include `diet_types` and `allergens`.
- **Properties**:
  - `diet_types`: Array of objects containing `name`, `color`, and `icon`.
  - `allergens`: Array of objects containing `name`, `color`, and `icon`.
- **SQL Source**: `product_diet_type` and `product_allergen` pivot tables.

### Requirement 2: Sticker Rendering (Frontend)
- **Description**: Displays the diet/allergen info in the product detail view using the existing asymmetrical sticker system.
- **Rules**:
  - Diet types are rendered before allergens.
  - Each item is a colored box with its name (no clickable interaction).
  - Rotations fluctuate between -15 and 10 degrees (brutalist style).
  - Background color is derived from the database record.

### Requirement 3: Automated Contrast
- **Description**: The text color (black or white) for each sticker must be calculated based on the brightness level of its solid background.
- **Formula**: `Luminance = 0.2126*R + 0.7152*G + 0.0722*B`.
- **Threshold**: luminance < 0.4 ? White (#fff) : Black (#000).
- **Fallback**: Black (#000).
