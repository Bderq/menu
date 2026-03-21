# Proposal: Media Operator Hub (Visual Audit Center)

## Problem Statement
Managing a menu with 100+ products requires a high-velocity workflow for image management. The current "Product Edit" flow is too slow for bulk operations, as it requires opening each product individually. There is no bird's-eye view to see which products are missing thumbnails or detail images.

## Proposed Solution: The Media Hub (Option B)
We will create a dedicated **"Media Hub"** resource in Filament. This is not a standard data entry resource, but an operational dashboard focused exclusively on visual assets.

### 1. Unified Table View
A single list containing all products with columns designed for visual status:
- **Product Name:** Standard text search/sort.
- **Thumbnail Column:** Inline `FileUpload` for the main `image_path`.
- **Card/Detail Column:** Inline `FileUpload` for the first item of the `gallery`.
- **Visual Status:** Color-coded badges (e.g., 🚨 Missing All, ⚠️ Missing Gallery, ✅ Complete).

### 2. Operational Features
- **In-Place Uploads:** Change images directly from the table without navigating away.
- **"The 100-Product Speed" Filter:** A one-click filter to only show products with missing visual assets.
- **Smart Binding Integration:** All uploads through this hub will follow the predictable naming (`slug.webp`) and optimization rules (300KB, 800px, WebP).

### 3. Navigation
Instead of hiding this under the standard product list, it will have its own top-level menu entry: **"Medya Operasyon"** to signify its importance in the content workflow.

## Benefits
- **Audit Efficiency:** Instantly see the percentage of catalog visual completion.
- **Lightning Fast Uploads:** Update dozens of product photos in minutes.
- **Consistency:** Enforces our image standards and filenames across the entire catalog.
