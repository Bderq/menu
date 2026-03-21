# Campaign System (Campaign Motor v1.0)

## Overview
A comprehensive system for managing time-based, rule-based marketing campaigns (Happy Hour, Bundles, Discounts) within the Street Pub menu.

## Requirements
-   **Admin Management:** Filament-based interface for creating/editing campaigns.
-   **Campaign Types:** 
    -   Bundle
    -   X Get Y
    -   Percentage Discount
    -   Fixed Price
-   **Targeting:** Specific product portions (e.g., Efes 50cl only).
-   **Logic Engine:** Centralized service to calculate active prices based on schedules and priority.
-   **Frontend UI:**
    -   Dynamic "Happy Hour" badges.
    -   Red-highlighted price bars for discounted portions.
    -   Crossed-out original prices.

## Technical Components
### Backend Models
-   `Campaign`: Main entity.
-   `CampaignItem`: Pivot for products/portions.
-   `CampaignSchedule`: Rules for active times/dates.

### Admin Panel
-   `CampaignResource`: Filament resource with dynamic forms.

### Pricing Logic
-   `CampaignService`: Central logic engine with `applyCampaigns($products)` method.

### UI Components
-   `PriceStack.jsx`: Renders pricing with discount logic.
-   `Badge.jsx`: Campaign/Happy Hour indicator.
