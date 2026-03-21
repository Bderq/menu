# Bulk Pricing Grid Implementation Plan

## Overview
Implement a high-performance "Excel-like" data grid for bulk managing product prices and portions across all stores. We will use **AG Grid Community Edition** integrated into a custom Filament Page.

## Project Type
- **WEB** (Filament Admin Panel)

## Tech Stack
- **Frontend**: Alpine.js, AG Grid Community
- **Backend**: Laravel Livewire (Filament)
- **Styling**: TailwindCSS (Brutalist Theme)

## Success Criteria
- [ ] User can view all products/variants in a grid.
- [ ] User can edit prices effectively (Excel style).
- [ ] Changes are saved automatically (batched).
- [ ] Grid matches the application's "Brutalist" aesthetic.

## File Structure
```
app/
  Filament/
    Pages/
      BulkPricing.php
resources/
  views/
    filament/
      pages/
        bulk-pricing.blade.php
  css/
    ag-grid-brutalist.css (or inline in blade)
```

## Task Breakdown

### Phase 1: Setup & Dependencies
- [ ] **Task 1.1**: Install AG Grid Community.
  - Command: `npm install ag-grid-community`
  - Input: NPM registry
  - Output: `node_modules` updated
  - Verify: `npm list ag-grid-community` returns version.

### Phase 2: Backend Logic (Livewire)
- [ ] **Task 2.1**: Create Filament Page `BulkPricing`.
  - Command: `php artisan make:filament-page BulkPricing`
  - Input: Filament resource generator
  - Output: `app/Filament/Pages/BulkPricing.php`
  - Verify: Page appears in sidebar.
- [ ] **Task 2.2**: Implement Data Fetching & Transformation.
  - Input: `Product` model, `Store` model
  - Output: `getGridData()` method returns JSON-friendly array.
  - Verify: `dd($data)` shows correct structure.
- [ ] **Task 2.3**: Implement `batchUpdate` method.
  - Input: Array of changes from frontend
  - Output: Database updates
  - Verify: Changing a price in UI updates DB.

### Phase 3: Frontend Implementation (AG Grid)
- [ ] **Task 3.1**: Create Blade View with Alpine.js & AG Grid.
  - Input: `resources/views/filament/pages/bulk-pricing.blade.php`
  - Output: Working grid with dummy data.
  - Verify: Grid renders on screen.
- [ ] **Task 3.2**: Connect Real Data to Grid.
  - Input: Livewire `$this->getGridData()`
  - Output: Grid populated with real products.
  - Verify: Row count matches DB.
- [ ] **Task 3.3**: Implement Autosave Logic (Alpine -> Livewire).
  - Input: `onCellValueChanged` event
  - Output: Livewire call to `batchUpdate`
  - Verify: Network tab shows requests on edit.

### Phase 4: Styling (Brutalist)
- [ ] **Task 4.1**: Style AG Grid.
  - Input: Custom CSS
  - Output: Grid looks "Brutalist" (Black/White, thick borders, mono font).
  - Verify: Visually check against design system.

## Phase X: Verification
- [ ] Lint Check
- [ ] Manual User Test (Edit 10 rows, check DB)
- [ ] Performance Test (>500 rows)
