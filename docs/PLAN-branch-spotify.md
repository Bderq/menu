# PLAN: Multi-Branch Spotify Integration (Option A)

## Context
The goal is to allow each branch (Store) to have a distinct Spotify account so they can display the currently playing song in the QR menu. We will implement "Option A", where Spotify credentials are saved securely in the database and managed via the Filament admin panel. 

## 1. Database Adjustments (Backend Specialist)
- **Migration:** Create open a migration to add Spotify columns to the `stores` table:
  - `spotify_client_id` (string, nullable)
  - `spotify_client_secret` (string, nullable)
  - `spotify_refresh_token` (string, nullable)
- **Model Update:** Add these columns to the `$fillable` or `guarded` structure in `app/Models/Store.php` if necessary. Considering these are API keys, the Casts for `spotify_client_secret` and `spotify_refresh_token` should optionally be set to `encrypted` to protect them.

## 2. Filament Admin Panel Updates (Frontend/Backend Specialist)
- **Form Schema:** Edit `StoreResource.php` to include inputs for the new fields within a visually distinct "Spotify Integration" Section or Fieldset.
- **Passwords fields:** Ensure `spotify_client_secret` and `spotify_refresh_token` are configured as password inputs to hide secrets visually from unauthorized admin view.

## 3. Spotify Service Refactoring (Backend Specialist)
- **Service Dependency:** Refactor `App\Services\SpotifyService.php` to accept a `Store` instance rather than falling back directly onto `config()`.
- **Token Fetching Check:** Modify `getAccessToken()` and `getNowPlaying()` to use the Store's specific `spotify_client_id`, `spotify_client_secret` and `spotify_refresh_token`.
- **Cache Separation:** Since multiple branches exist, modify the caching keys to be store-specific (e.g. `spotify_access_token_{store_id}`, `spotify_now_playing_{store_id}`) to prevent crosstalk between branches.

## 4. Controller Adjustment
- **MenuController:** Update the `nowPlaying()` method in `App\Http\Controllers\MenuController.php` to receive the `$store_slug`, find the correct store, and pass it down into the refactored `SpotifyService`.
- **Routing:** Ensure the frontend API route `/menu/{store_slug}/now-playing` accommodates the branch slug dynamically (it might already).

## 5. Frontend adjustments
- Verify `Index.jsx` passes its `store.slug` to the `NowPlaying` AJAX endpoint appropriately. 

## Socratic Gate / Questions Before Creating:
- **Encryption:** Would you like to use Laravel's native Eloquent encryption (the `encrypted` cast) on `spotify_client_secret` and `spotify_refresh_token` in the database?
- **Global Fallback:** If a branch hasn't configured Spotify, should it fall back to the global env variables, or simply display no song?
