# Bulk Pricing System

## Overview
A high-performance "Excel-like" data grid for bulk managing product prices and portions across all stores.

## Requirements
-   **Grid Interface:** Using **AG Grid Community Edition**.
-   **Bulk Management:** View all products/variants in a single grid.
-   **Efficient Editing:** Keyboard-friendly, Excel-style cell editing.
-   **Autosave:** Changes are saved automatically (batched update).
-   **Performance:** Capable of handling 500+ rows smoothly.

## Technical Components
### Admin Page
-   `app/Filament/Pages/BulkPricing.php`: Filament/Livewire backend for data fetching and batch updates.
-   `resources/views/filament/pages/bulk-pricing.blade.php`: Blade view containing the AG Grid and AlpineJS logic.

### Styling
-   **Theme:** "Brutalist" design (Black/White, thick borders, monochrome aesthetics).
-   **Custom CSS:** Specialized AG Grid CSS to match the system's design tokens.

### Data Flow
1. `getGridData()` fetches products/portions as a flat JSON array.
2. `onCellValueChanged` in AG Grid triggers an AlpineJS update to Livewire.
3. `batchUpdate()` handles database persistence.
