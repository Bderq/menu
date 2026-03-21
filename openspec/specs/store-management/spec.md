# Store Management

## Overview
A multi-store management system allowing the same menu to have different prices and availability across different physical locations.

## Core Models
-   `Store`: Physical location entity.
-   `StoreProduct`: Pivot for managing store-specific product options.
-   `StoreProductPortion`: Defines the actual pricing for a specific version of a product at a specific store.

## Key Features
-   **Store-Specific Pricing:** Ability to set different prices for the same product in different stores.
-   **Availability Control:** Toggle product visibility based on store inventory.
-   **Portion Management:** Individual price points for size-based variants (e.g., 50CL vs 33CL).

## Data Flow
Each menu request is scoped by a `store_id`, which filters the available `StoreProductPortion` records and applies active `Campaigns` specific to that store.
