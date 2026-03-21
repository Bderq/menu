# Proposal: Smart Image Bindings

## Problem Statement
When running `php artisan migrate:fresh --seed` during development, the database records are wiped, but the physical image files remains in `storage/app/public`. Currently, Filament generates random filenames (e.g., `01H...webp`), making it impossible for a Seeder to know which file belongs to which product after a database reset. This results in missing images in the UI and requires manual re-uploading every time the database is reset.

## Proposed Solution: Predictable Naming & Auto-Discovery
We will implement "Smart Bindings" to create a permanent link between physical files and database records based on naming conventions.

### 1. Predictable Naming in Filament
Modify the `FileUpload` components to use the product/campaign slug as the filename. Instead of random strings, a burger image will be saved as `classic-burger.webp`.
- Method: `->getUploadedFileNameForStorageUsing()`

### 2. Auto-Discovery in MenuSeeder
Update the `MenuSeeder` to check if a file matching the product/category slug already exists in the storage directory before creating a new record. If a match is found, the Seeder will automatically populate the `image_path` field.
- Logic: `if (Storage::disk('public')->exists("products/thumbnails/{$slug}.webp"))`

## Benefits
- **Idempotent Development:** Resetting the DB no longer breaks the visual menu.
- **Master Data Persistence:** Once a photo is uploaded and named correctly, it becomes part of the project's permanent "seedable" state.
- **Developer Experience:** No more manual re-uploads after schema changes.

## Affected Components
- `App\Filament\Resources\Products\Schemas\ProductForm.php`
- `App\Filament\Resources\CampaignResource.php`
- `Database\Seeders\MenuSeeder.php`
