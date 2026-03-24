# Proposal: Media Operator Hub (Visual Audit Center)

## Problem Statement
Managing a menu with 100+ products requires a high-velocity workflow for image management. The current "Product Edit" flow is too slow for bulk operations, as it requires opening each product individually. There is no bird's-eye view to see which products are missing thumbnails or detail images.

## Proposed Solution: The Media Hub (Option B)
We will create a dedicated **"Media Hub"** resource in Filament. This is not a standard data entry resource, but an operational dashboard focused exclusively on visual assets.

### 1. Unified Table View
A single list containing all products with columns designed for visual status:
- **Product Name:** Standard text search/sort.
- **Thumbnail Column:** High-speed inline drag-and-drop for the main `image_path`.
- **Card/Detail Column:** High-speed inline drag-and-drop for multiple items in the `gallery`.
- **Visual Status:** Color-coded badges (e.g., 🚨 Missing All, ⚠️ Missing Gallery, ✅ Complete).

### 2. Operational Features
- **In-Place Uploads:** Change images directly from the table by dragging from PC (modal-free).
- **Automated Optimization:** Powered by Intervention Image v3. Auto-converts to WebP, resizes to 800px, and enforces SEO-friendly naming.
- **"The 100-Product Speed" Filter:** A one-click filter to only show products with missing visual assets.
- **Live Feedback:** Instant preview and progress spinners during upload.

### 3. Navigation
Instead of hiding this under the standard product list, it will have its own top-level menu entry: **"Medya Operasyon"** to signify its importance in the content workflow.

## Benefits
- **Audit Efficiency:** Instantly see the percentage of catalog visual completion.
- **Lightning Fast Uploads:** Update dozens of product photos in minutes.
- **Consistency:** Enforces our image standards and filenames across the entire catalog.
