## Why
Products need to display dietary constraints (Vegan, Vegetarian, Gluten-Free) and allergens so customers can make informed choices. This change implements the backend admin panel side in a dynamic and flexible manner, ensuring safe integration into the live database.

## What Changes
- Add new `allergens` table (dynamic tags) and `allergen_product` pivot table.
- Add new `diet_types` table and `diet_type_product` pivot table.
- Introduce `Allergen` and `DietType` models alongside `Product`'s BelongsToMany relations.
- Add `AllergenResource` and `DietTypeResource` in the Filament admin panel to allow admins to manage the master list of diet labels and allergens.
- Modify `ProductResource` forms and info lists to include a multiple-select element for associating tags.

## Capabilities

### New Capabilities
- `diet-allergens-management`: Defines the ability to manage dynamic diet types and allergens and assign them to Master Products.

### Modified Capabilities

## Impact
- **Database Schema**: New tables (`allergens`, `diet_types`, `allergen_product`, `diet_type_product`). Does not mutate/drop existing live tables.
- **Admin Panel**: New Filament Resources, modified `ProductResource`.
- **API/Frontend**: Prepares data to be accessed via standard models; read-only to frontend.
