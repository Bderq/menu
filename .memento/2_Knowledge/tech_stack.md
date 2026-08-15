# Tech Stack Detail

<!-- AUTO-DETECTED, verify -->

- **Backend:** Laravel 12.x, PHP ^8.2
- **Admin panel:** Filament v5.1 (`app/Filament/Resources`, `Pages`, `Widgets`)
- **Frontend:** React 19.x + Inertia.js v2 (`resources/js/Pages`, `resources/js/Components`)
- **Styling:** Tailwind CSS v4 — CSS-based config (no `tailwind.config.js`), tokens live in [resources/css/app.css](../../resources/css/app.css) via `@theme`
- **Animations:** Framer Motion v12
- **Build:** Vite v7 + laravel-vite-plugin
- **Other libs:** ag-grid-community (data grids), lucide-react (icons), tightenco/ziggy (route helpers in JS), intervention/image, solution-forest/filament-tree
- **Testing:** PHPUnit 11 (`tests/Feature`, `tests/Unit`), no JS test runner configured
- **Dev workflow:** `composer run dev` runs `php artisan serve` + queue listener + `artisan pail` (logs) + `npm run dev` concurrently

Domain: multi-branch (multi-store) digital QR menu system with a campaign engine (fixed price / percentage / bundle / X-get-Y discounts) and time-based scheduling including overnight windows.
