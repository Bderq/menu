# Core Infrastructure

## Overview
This specification defines the foundational technical architecture and structural patterns for the Menu Project.

## Tech Stack
-   **Backend:** Laravel 12.0 (PHP 8.2+)
-   **Admin Panel:** Filament ^5.1
-   **Frontend:** React 19, Inertia.js 2.0
-   **Styling:** Tailwind CSS 4.0
-   **Build Tool:** Vite 7.0
-   **State/UI:** Framer Motion, Lucide React, AG Grid Community
-   **Database:** MySQL (per .env config)

## Directory Structure
-   `app/Models/`: Eloquent models defining the core domain.
-   `resources/js/Pages/`: Inertia React page components.
-   `database/`: Migrations, seeders, and factories.
-   `openspec/`: OpenSpec documentation and change tracking.

## Core Domain Models
-   **Product:** Core menu item definition.
-   **Category:** Product organization.
-   **Store:** Physical location mapping.
-   **Campaign:** Marketing and pricing rules (includes CampaignItem, CampaignSchedule).
-   **StoreProduct / StoreProductPortion:** Store-specific product and pricing data.
-   **User:** Authentication and authorization.
