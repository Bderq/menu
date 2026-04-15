## Why

Customers need to see dietary types (e.g., Vegan, Gluten-Free) and allergen information (e.g., Peanuts, Dairy) directly on the product menu to make informed and safe ordering decisions. This change exposes the newly created Admin Matrix data to the frontend UI.

## What Changes

- **Product API Extension**: Update the menu data payload to include `diet_types` and `allergens` for each product.
- **Product Detail UI**: Enhance the product detail drawer to display these attributes as "stickers" (tags) next to the product image.
- **Contrast Handling**: Implement automatic text contrast (black/white) based on the background color of each tag to maintain readability.

## Capabilities

### New Capabilities
- `product-diet-safety`: Displays dietary preferences and allergen warnings on the product detail view using the established brutalist sticker aesthetic.

### Modified Capabilities
<!-- No existing spec-level requirement changes, this is an extension of product display. -->

## Impact

- **App\Services\MenuService**: Modification to include relationships and pivot data in the JSON response.
- **resources/js/Pages/Menu/Index.jsx**: Addition of React logic to render the new data in the product detail section.
- **Performance**: Negligible impact due to eager loading of relationships.
