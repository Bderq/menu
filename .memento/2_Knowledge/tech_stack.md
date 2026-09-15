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

## External services (2026-09-15)

- **Spotify Web API** — per-store credentials in the `stores` table
  (`spotify_client_id` / `spotify_client_secret` / `spotify_refresh_token`;
  last two are `encrypted` casts). Each store has its **own Spotify
  developer app**, so redirect URIs and scopes are configured per app.
  Tokens must carry `user-modify-playback-state` for queueing, plus the
  two read scopes for "now playing". Used by `app/Services/SpotifyService.php`.
- **Google Gemini** — song-request detection, REST `generateContent` with
  a JSON response schema (no SDK). `GEMINI_API_KEY` / `GEMINI_MODEL`.
- **Telegram Bot API** — guest-message notifications and the inline
  buttons that queue a track. `TELEGRAM_BOT_TOKEN` / `TELEGRAM_CHAT_ID`
  (currently a group) / `TELEGRAM_WEBHOOK_SECRET`.

## Server-side runtime (not in git)

Queue driver is `database`. Two systemd units were created on the host
and are **not** part of the repo — a fresh server needs them recreated:

- `qr-menu-queue.service` → `php artisan queue:work` (the project had no
  queue worker at all before 2026-09-15)
- `qr-menu-telegram.service` → `php artisan telegram:poll` (long-polling
  for button presses; the webhook path exists but Telegram could not
  reliably reach this server)

Related but separate: `/var/www/spotify-agent` (music.crashtheroof.com) is
an independent Laravel app that schedules Spotify playback per location.
qr-menu does **not** call it — see decisions.md 2026-09-15.
