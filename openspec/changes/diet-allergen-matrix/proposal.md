## Why

Managing diet types and allergens per product through individual product edit forms is slow and error-prone, especially with a large menu. A dedicated matrix view lets admins assign tags to all products in a category at once, dramatically reducing friction.

## What Changes

- Add new Filament Custom Page `DietAllergenMatrix` that renders a category-filtered product × tag matrix.
- Admin selects a category from a dropdown; the table updates to show products in that category as rows and all diet types + allergens as columns.
- Each cell is a checkbox — clicking instantly attaches or detaches the pivot relationship (no Save button required).
- Column headers use the `color` field from `DietType`/`Allergen` models; text color is auto-contrasted (white on dark, black on light).
- Diet type columns (3) appear first, separated by a divider from allergen columns (12).
- New navigation link added under the 'Ürün Yönetimi' group.

## Capabilities

### New Capabilities
- `diet-allergen-matrix-page`: Admin UI page for bulk matrix management of diet types and allergens per product, filtered by category.

### Modified Capabilities

## Impact

- **Admin Panel**: New Filament Page + Blade view.
- **Database**: Uses existing `diet_type_product` and `allergen_product` pivot tables via `attach`/`detach` — no schema changes.
- **Models**: No model changes required; relationships already exist on `Product`.
