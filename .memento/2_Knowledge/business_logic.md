# Business Logic

<!-- AUTO-DETECTED, verify -->

## Campaign scheduling supports overnight windows
> [app/Services/CampaignService.php:91-113](../../app/Services/CampaignService.php#L91-L113) — a campaign schedule's `start_time`/`end_time`
> can wrap past midnight (e.g. 22:00–04:00). The code branches on whether
> `start_time <= end_time` (same-day window) vs. `start_time > end_time`
> (overnight window, checked against both "yesterday" and "today" day-of-week
> entries). The same logic is re-implemented as a query builder chain in
> `getActiveCampaigns()` further down the same file. Any new time-window
> logic touching campaigns must replicate this same-day/overnight branching,
> not assume a simple range check.

## Percentage-discount math is duplicated across ~4 call sites, not shared
> `discount = (originalPrice * campaign.value) / 100; newPrice = originalPrice - discount`
> is reimplemented independently in:
> [CampaignService.php:218-219](../../app/Services/CampaignService.php#L218-L219),
> [CampaignService.php:249-250](../../app/Services/CampaignService.php#L249-L250),
> [MenuService.php:264-265](../../app/Services/MenuService.php#L264-L265), and
> [MenuService.php:293-294](../../app/Services/MenuService.php#L293-L294) (this last one inlined as
> `optPrice - ((optPrice * campaign.value) / 100)`, same formula, different
> expression shape). If the discount formula ever changes, all four sites
> need updating — there's no shared helper today.

## Campaign types (from `app/Enums/CampaignType.php`)
> Five types exist in code: `FIXED_PRICE`, `PERCENTAGE`, `BUNDLE`, `X_GET_Y`,
> and `COLLECTIVE`. The README's feature list only mentions four (Fixed
> Price, Percentage, Bundle, X-Get-Y) — `COLLECTIVE` (tiered/group pricing,
> see the `collective_tiers`/`tiers[0]['price']` handling next to each
> PERCENTAGE branch above) is implemented but undocumented in the README.
> Confirm with the user whether that's intentional (unfinished/internal
> feature) before treating the README's list as authoritative.

## "Ses Verin" guest messages can be song requests (2026-09-15)
> Every guest message goes through `ProcessGuestMessage` (queued). Gemini
> classifies it: if it is **not** a song request — or the store has no
> Spotify credentials — a plain notification goes to Telegram and nothing
> else happens. If it **is** a song request, the extracted artist/title is
> searched on **that store's own** Spotify account and a `song_requests`
> row is created with the top 3 candidates.
>
> A request is only queued when a human presses the Telegram button —
> nothing is ever added to playback automatically. Status flow:
> `pending → processing → queued | dismissed` (plus `not_found` when
> Spotify returns no match). `processing` is a lock, not a resting state:
> a failed attempt returns the row to `pending` so the buttons stay live.
>
> Requests arrive **only from the menu's "Ses Verin" form**. Messages
> typed into the Telegram group itself are not read by the system.
>
> Guest messages are rate-limited to **2 per day per visitor per store**,
> keyed by the `qr_menu_visitor_id` cookie (falls back to IP when the
> cookie is missing), plus a **20/hour per IP** ceiling against abuse.
> The limiter lives in `AppServiceProvider::boot()`, not `routes/web.php`
> — see decisions.md 2026-09-15. Keying by IP alone was wrong: everyone
> on a venue's wifi shares one public IP and therefore one quota.

## Open questions (couldn't resolve from code)
- <TODO: is there a canonical "business day" boundary anywhere (e.g. for
  analytics/reporting cutoffs), or does everything use calendar midnight
  except campaign scheduling?>
- <TODO: any pricing rules around combining multiple active campaigns on
  the same product — first-match, stacking, or highest-discount-wins?>
