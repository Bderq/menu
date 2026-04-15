## Context

The backend already has a `Product` model with `dietTypes` and `allergens` relationships (M2M). The admin panel allows managing these via a matrix. The frontend currently has a "sticker" column in the product detail view using a fixed `tags` array.

## Goals / Non-Goals

**Goals:**
- Feed diet and allergen data to the frontend via the `MenuService`.
- Display these as colorful stickers in the product drawer.
- Handle dark/light contrast and responsive stacking.

**Non-Goals:**
- Adding filtering logic to the main menu (already exists for some, but full dynamic matrix filtering is out of current scope).
- Modifying the product image display.

## Decisions

- **Eager Loading**: We will load `dietTypes` and `allergens` relationships in the main category query in `MenuService` to avoid N+1 queries.
- **Frontend Merging**: We will append these new attributes to the existing sticker rendering loop in `Index.jsx`.
- **Luminance Utility**: We will implement a small JS helper in the component (or use a shared hook) to calculate text color (black vs white) based on the hex color from the database.
- **Stacking Logic**: We will use a standard `flex-col` stack to show all tags vertically, maintaining the asymmetric rotation for a consistent brutalist look.

## Risks / Trade-offs

- **Color Contrast**: Some colors from the DB may look bad on specific backgrounds. Mitigation: Use full-range luminance check (0.2126*R + 0.7152*G + 0.0722*B).
- **Column Length**: A product with many allergens may cause the drawer to scroll more. Mitigation: The drawer is already full-screen and scrollable; no limit is imposed per user request.
