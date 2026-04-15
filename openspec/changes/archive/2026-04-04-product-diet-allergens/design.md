## Context
We are implementing dynamic diet types and allergens to the `Product` entity in our QR Menu system. Currently, the `Product` model has simple `tags` and `badges` JSON fields, but this does limit scalability and manageability within the Filament Admin interface for standard, unified categories. 
The database is actively live, requiring new schema migrations to be safe and additive-only.

## Goals / Non-Goals

**Goals:**
- Add completely relational tables for `allergens` and `diet_types`.
- Add pivot tables (`allergen_product`, `diet_type_product`).
- Enhance the Filament Admin Panel so that administrators can globally manage these tags and assign them to Master Products.
- Provide a safe migration path that does not mutate or drop any existing column.

**Non-Goals:**
- Store-specific overrides for allergens and diet types (decided against to keep management simple).
- Frontend implementations or filtering (to be handled separately, current priority is Admin).

## Decisions
- **Dynamic Relational Model vs JSON properties**: We decided to go with completely separate Models (`Allergen`, `DietType`) mapped via Many-to-Many pivot tables (`allergen_product`, `diet_type_product`). While a JSON structure (`tags` or `badges`) is easier, it lacks constraint, type-safety, and the ability to easily add globally manageable properties (like color, icon, translation).
- **Master Product Scope**: Diet types and allergens are tied directly to the `App\Models\Product` model instead of `App\Models\StoreProduct`. This ensures consistency across all stores for the same Master Product.

## Risks / Trade-offs
- **Live Database Migrations**: Risk of accidentally affecting the `products` table. Mitigation: The migrations will only use `Schema::create` to build the required tables and not involve `dropIfExists` logic around critical business data.
