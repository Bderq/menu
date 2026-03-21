# Proposal: Image Optimization Standards (The 100-Product Speed)

## Problem Statement
A menu with 100+ products loading unoptimized 2MB images would require downloading ~200MB of data. This causes severe performance degradation, drains mobile data, overheats devices, and ruins the user experience. 

## Proposed Solution (Ideal Scenario: WebP + 800px + Lazy Load)
We will enforce aggressive optimization rules to ensure the 100-product menu loads instantly and consumes ~15MB total.

Implementation Requirements:
1. **Frontend Lazy Loading:** Ensure all images on the menu page use lazy loading so off-screen images are not downloaded initially.
2. **Aggressive File Constraints (Filament):** 
   - Cap maximum upload size at **300KB** (`->maxSize(300)`).
   - Convert all uploads to **WebP** (`->optimize('webp')`).
   - Limit dimensions to a maximum width of **800px** (`->imageResizeTargetWidth(800)`).
3. **Batch Optimization Command:** Create a one-time Artisan command (`php artisan media:optimize`) that loops through all existing models, resizes their images to 800px, converts them to WebP, and updates the database paths.

## Affected Components
- `App\Filament\Resources\Products\Schemas\ProductForm.php`
- `App\Filament\Resources\CampaignResource.php`
- Frontend React Components
- New Console Command: `App\Console\Commands\OptimizeMedia.php`
