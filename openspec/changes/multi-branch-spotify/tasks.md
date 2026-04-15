## 1. Database & Model Updates

- [x] 1.1 Create a new database migration to add `spotify_client_id`, `spotify_client_secret`, and `spotify_refresh_token` tightly to the `stores` table as string nullable fields.
- [x] 1.2 Run `php artisan migrate` to apply the migrations.
- [x] 1.3 Update the `App\Models\Store` model to configure `$fillable` or `$guarded` properties correctly to permit these fields to be saved.
- [x] 1.4 Update the `App\Models\Store` model to define standard Laravel `casts()` to set `spotify_client_secret` and `spotify_refresh_token` as `'encrypted'`.

## 2. Filament Admin Panel Configurations

- [x] 2.1 Edit `app/Filament/Resources/StoreResource.php` to include a Section titled "Spotify Integration".
- [x] 2.2 Add inputs for `spotify_client_id` (TextInput), `spotify_client_secret` (TextInput with `password()` and `revealable()`), and `spotify_refresh_token` (TextInput with `password()` and `revealable()`).

## 3. Spotify Service Isolation

- [x] 3.1 Refactor `App\Services\SpotifyService` to remove global configuration dependencies from `__construct`.
- [x] 3.2 Update `getAccessToken` and `getNowPlaying` methods in `SpotifyService` to expect a `Store $store` parameter.
- [x] 3.3 Ensure the cache keys constructed in `SpotifyService` explicitly append the `$store->id` to avoid crossover (e.g. `spotify_access_token_{$store->id}`).

## 4. Frontend & Controllers

- [ ] 4.1 Check `Routes/web.php` and or API routes. The currently existing logic might bind `/menu/{store_slug}/now-playing`, confirm this, or update `routes.php`.
- [ ] 4.2 Update `App\Http\Controllers\MenuController`'s `nowPlaying` method to accept the store slug from the request route (`$store_slug`), resolving the `Store` to fetch Now Playing using the updated service logic.
- [ ] 4.3 Verify `resources/js/Pages/Menu/Index.jsx` passes its assigned `store.slug` correctly when hitting the API url endpoint for tracking the currently playing audio.
