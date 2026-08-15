# Dev Guidelines

<!-- AUTO-DETECTED, verify -->

## Stack
- Backend: Laravel 12 (PHP 8.2+)
- Admin: Filament v5
- Frontend: React 19 + Inertia.js v2, Tailwind v4
- See [tech_stack.md](../2_Knowledge/tech_stack.md) for full detail.

## Conventions
- No `.eslintrc*`, `.prettierrc*`, `pint.json`, or `phpstan.neon` found — no enforced linter/formatter config in the repo; Laravel Pint is a dev dependency but runs with defaults.
- Business logic lives in `app/Services/` (e.g. `CampaignService`, `MenuService`), not in controllers — controllers under `app/Http/Controllers` stay thin.
- Filament admin resources under `app/Filament/Resources/<Model>Resource.php` with a matching `<Model>Resource/` subfolder (Schemas, Pages) — see `app/Filament/Resources/Products/Schemas/ProductForm.php` for the pattern.
- Domain enums live in `app/Enums/` (e.g. `CampaignType`, `CategoryType`).
- React pages live in `resources/js/Pages/`, shared components in `resources/js/Components/`.
- Feature plans are tracked as standalone docs: `docs/PLAN-*.md` — check there before assuming a feature is undocumented.
- Tests: PHPUnit under `tests/Feature` and `tests/Unit`, no frontend test runner set up.

## Things to verify with the user
- <naming conventions beyond the above, PR/commit conventions, deployment process>
