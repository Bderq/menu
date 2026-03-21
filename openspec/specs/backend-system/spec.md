# Backend System & Data Flow

## Overview
This specification documents the current implementation of the backend logic that powers the QR Menu, focusing on how data is retrieved, structured, and served to the React frontend.

## 1. Request Flow
Standard interaction flow for menu delivery:
1. **Route Entry**: User accesses `/{store_slug}`.
2. **Controller Activation**: `MenuController@index` is triggered.
3. **Store Identification**: Validates `store_slug` against `stores` table.
4. **Data Aggregation**:
   - Categories are fetched recursively (Main -> Group -> Sub).
   - Products are filtered by `store_id` and `is_active` status.
   - Prices are resolved via `StoreProductPortion`.
5. **Campaign Layer**: `CampaignService` processes the menu tree to apply dynamic pricing/badges.
6. **Delivery**: Data is passed via Inertia to `Menu/Index.jsx`.

## 2. Menu Structure (The Tree)
The system categorizes products into a three-level hierarchy to support the tabbed navigation of the frontend:

- **Level 1 (Main Category)**: `Food`, `Drink`, `Campaign`. Determines the top-level navigation tabs.
- **Level 2 (Category Group)**: `Beer`, `Burger`, `Pizza`. These appear as the headers within the active tab.
- **Level 3 (Sub-Category)**: `Draft Beer`, `Bottled Beer`. Groupings within a header for finer organization.

## 3. Pricing Resolution Logic
Prices are NOT stored directly on the `Product` model to support multi-store flexibility:
- **Product**: Global name, description, and base image.
- **StoreProduct (Pivot)**: Store-specific overrides (custom name/image) and local active status.
- **StoreProductPortion**: The actual price entity.
  - Supports multiple portions per product (e.g., Small, Medium, Large).
  - Resolved in `MenuController@formatProduct`.

## 4. Campaign Motor Logic
Handled by `CampaignService@applyCampaigns`:
- **Filtering**: Retains campaigns where `is_active = true` and `stores` linkage exists.
- **Time Check**: Validates against `start_date`, `end_date`, and `CampaignSchedule` (Weekly/Daily/Hourly).
- **Rule Application**:
  - `percentage`: Deducts % from base price.
  - `fixed_price`: Replaces price with override.
  - `bundle`: Sets a fixed price for the specific item combo.
  - `x_get_y`: Informs the UI about multi-buy deals (frontend logic displays the badge).

## 5. Current Bottlenecks
- **Query Density**: `MenuController` performs multiple nested queries during tree building (N+1 risks).
- **Controller Complexity**: High CC (Cyclomatic Complexity) in `index` method due to formatting and best-seller logic.
- **Stateless Delivery**: Every request rebuilds the entire tree from the database.

## 6. Planned Refinements
- [ ] **Service Extraction**: Move tree-building logic to `MenuService`.
- [ ] **Resource Layer**: Implement `JsonResource` for consistent API structure.
- [ ] **Cache Implementation**: Mağaza bazlı `Cache::tags(['menu', 'store_1'])` kullanımı.
- [ ] **Repository Pattern**: Abstract data retrieval from Eloquent for better testing.
