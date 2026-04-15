## 1. Database & Models

- [x] 1.1 Create migration for `allergens` and `diet_types` tables with `name`, `icon`, `color`.
- [x] 1.2 Create migration for pivot tables `allergen_product` and `diet_type_product`.
- [x] 1.3 Create `Allergen` and `DietType` Eloquent models with pivot relationships and `$guarded=[]`.
- [x] 1.4 Update `Product` model to define `allergens()` and `dietTypes()` `BelongsToMany` relationships.

## 2. Filament Admin Resources

- [x] 2.1 Generate `AllergenResource` for Filament and register it to the existing navigation.
- [x] 2.2 Generate `DietTypeResource` for Filament and register it to the existing navigation.
- [x] 2.3 Modify `ProductResource` schema to include multi-select relation inputs for `allergens` and `dietTypes`.
