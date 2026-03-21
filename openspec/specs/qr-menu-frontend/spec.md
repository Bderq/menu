# QR Menu Frontend

## Overview
The customer-facing digital menu interface, designed to be fast, responsive, and visually striking. The current primary interface is based on a "Brutalist Sticker Style" aesthetic.

## Implementation Details
-   **Current Active View:** `resources/js/Pages/Menu/Index.jsx`
-   **Style:** Brutalist Sticker Style (Thick borders, heavy shadows, high-contrast, bold typography).
-   **Experimental Views:** `IndexV2.jsx`, `IndexV3.jsx`, `IndexV4.jsx` (iterations and style explorations).

## Core Design Principles (Index.jsx)
-   **Sticker Navigation:** Category tabs are designed to look like stickers (e.g., the red "KAMPANYA" sticker).
-   **Brutalist Interface:** Heavy 4-6px black borders, offset shadows (`shadow-[4px_4px_0_0_#000]`).
-   **Responsive Layout:** Single-column scroll for mobile, utilizing IntersectionObservers for smooth navigation.
-   **Micro-Animations:** Framer Motion used for page transitions and drawer interactions.

## Key Features
-   **Dynamic Tab System:** Switching between Drinks, Food, and Campaigns.
-   **Active Campaign Billboard:** Visualizing active store campaigns with a dedicated grid.
-   **Product Catalog:** Category-grouped products with portion-based pricing.
-   **Interaction Drawer:** A sidebar/bottom sheet for filters and venue-specific information.

## Technical Components
### Inertia Pages
-   `resources/js/Pages/Menu/Index.jsx`: Primary entry point.

### Core Components (Referenced in Index.jsx)
-   `StreetLayout`: Main layout wrapper.
-   `MenuInteractionDrawer`: Filters and general info.
-   `DefaultCampaignCard`: Detail view for campaign items.
-   ` LucideIcons`: Icon library for visual cues.
