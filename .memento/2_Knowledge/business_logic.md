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

## Open questions (couldn't resolve from code)
- <TODO: is there a canonical "business day" boundary anywhere (e.g. for
  analytics/reporting cutoffs), or does everything use calendar midnight
  except campaign scheduling?>
- <TODO: any pricing rules around combining multiple active campaigns on
  the same product — first-match, stacking, or highest-discount-wins?>
