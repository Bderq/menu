## 1. Filament Page & Backend

- [x] 1.1 Create `app/Filament/Pages/DietAllergenMatrix.php` with Livewire properties: `$selectedCategoryId`, and methods `getCategories()`, `getMatrixData()`, `toggleTag($productId, $type, $tagId)`, `getTextColor($hexColor)`.
- [x] 1.2 Register the page in navigation under 'Ürün Yönetimi' group with icon `heroicon-o-table-cells`.

## 2. Blade View

- [x] 2.1 Create `resources/views/filament/pages/diet-allergen-matrix.blade.php` with a top bar (category select via `wire:model.live`) and a horizontally scrollable matrix table.
- [x] 2.2 Render DietType columns (colored headers, `wire:click` checkboxes), then a thick divider `<th>` spacer, then Allergen columns.
- [x] 2.3 Checked state: fill cell with tag's color (dark bg); unchecked state: white/gray bg. Use `getTextColor()` for readable labels in header.
