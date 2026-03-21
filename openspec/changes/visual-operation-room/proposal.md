# Proposal: Visual Operation Room (Drop & Done Experience)

## Problem Statement
The current Media Hub uses a standard table which requires a click to open a modal before uploading. For 100+ products, this "click-wait-drop" cycle is too slow. Users expect a more fluid, modern experience where they can simply throw a file at a product and move to the next one immediately.

## Proposed Solution: The Visual Grid Manager
We will build a specialized **Custom Filament Page** that replaces the list-view with a high-performance visual grid.

### 1. The "Live-Grid" Interface
- **Product Cards:** Each product is represented by a card containing its name and two distinct dropzones (Thumbnail and Detail).
- **Zero-Click Uploads:** Each zone on the card is an active listener. Dropping a file directly on the "Thumbnail" zone of a "Burger" card triggers the upload instantly.
- **Visual Feedback:** 
  - Cards highlight when a file is hovered over them.
  - A subtle progress bar appears directly inside the card during upload.
  - The image updates in real-time once the upload is completed.

### 2. Technical Strategy
- **Framework:** Custom Filament Page powered by a **Livewire** component.
- **Drag & Drop Logic:** Use **AlpineJS** or a lightweight JS library (like Dropzone.js) integrated into the Livewire component to catch global window drops and route them to specific product IDs.
- **Backend Bridge:** A dedicated Livewire action will handle the temporary file, run our optimization logic (WebP, resizing), and update the `Product` model.

### 3. UX Improvements
- **Audit Mode:** A toggle to "Highlight Empty Only" which dims products that already have images, making the missing ones pop.
- **Bulk Refinement:** Since the grid is compact, users can see 20+ products at once, making it the ultimate tool for visual quality control.

## Success Criteria
- A user should be able to update 5 different products by dragging 5 different files to their respective cards **without clicking any buttons** on the page.
