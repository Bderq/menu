## Context

The QR Menu admin panel (Filament 5, Laravel 12) has `Product`, `DietType`, and `Allergen` models with established many-to-many relationships via `diet_type_product` and `allergen_product` pivot tables. Products are managed through individual forms, but bulk tag assignment across many products in a category is not possible today.

The existing `BulkPricing.php` custom Filament Page demonstrates the pattern for grid/matrix UIs using Livewire + Alpine.js within Blade views.

## Goals / Non-Goals

**Goals:**
- Render a scrollable matrix table: rows = products in selected category, columns = diet types (3) then allergens (12).
- Instant toggle via Livewire `toggleTag()` — no batch save required.
- Column headers colored with `Allergen.color` / `DietType.color`; text auto-contrasted to ensure readability.
- Category selector dropdown at top of page (Livewire reactive).
- Vertical divider between diet type columns and allergen columns.

**Non-Goals:**
- Product thumbnails (minimalist view confirmed).
- Store-level overrides for diet/allergen.
- Frontend (React/Inertia) changes.

## Decisions

- **Custom Filament Page (not a Resource):** Same pattern as `BulkPricing.php`. A Resource would not suit the matrix UI pattern; a custom Page with a Blade view gives full layout control.
- **Livewire-driven with `wire:click`:** Each checkbox calls `toggleTag($productId, $type, $tagId)` on the server directly. No Alpine.js batch queue needed since operations are cheap pivot attach/detach.
- **`attach`/`detach` not `sync`:** Preserves other products' associations; safe for live database.
- **Color contrast formula:** If hex color's relative luminance < 0.4 → white text (`#FFFFFF`), else black text (`#000000`). Computed in Blade with a PHP helper method on the Page class.

## Risks / Trade-offs

- **Large categories:** If a category has 100+ products, the table becomes very tall. Mitigation: CSS `overflow-x: auto` on the table wrapper; category filter reduces visible rows.
- **Livewire round-trips per click:** Each checkbox is a server call. For matrix page this is acceptable since changes are infrequent and immediate feedback is valuable.
