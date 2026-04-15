## Why

The current campaign structure creates significant clutter when trying to offer volume discounts (e.g., "4-pack", "8-pack", "12-pack"). Creating individual campaign cards for each volume tier fills the campaign gallery with near-identical entries, overwhelming the user. A "Collective" (Tiered) campaign type is needed to group these volume-based price breaks under a single, unified offering.

## What Changes

- Add a new `COLLECTIVE` (or `TIERED`) value to the `CampaignType` enum.
- Add a `tiers` JSON column to the `campaigns` database table to store quantity/price breakdowns.
- Update the Filament `CampaignResource` to display a Repeater field for managing `tiers` when the `COLLECTIVE` type is selected.
- Update `CampaignService` and `MenuService` to attach the tier data to the formatted products.
- Redesign the frontend campaign card (`DefaultCampaignCard.jsx`) and product details drawer (`Index.jsx`) to display a price/quantity matrix table instead of a single price tag when rendering a `COLLECTIVE` campaign.

## Capabilities

### New Capabilities
- `collective-campaigns`: Defines the structural requirements, management interface, and frontend rendering logic for tiered/volume-based discount campaigns.

### Modified Capabilities

- 

## Impact

- Database: `campaigns` table migration to add `tiers` column.
- Models: `Campaign.php` cast updates for `tiers`.
- Services: `CampaignService.php` and `MenuService.php` modified to include tier payload.
- Admin: `CampaignResource.php` form schematic changes.
- Frontend: `Index.jsx` and `DefaultCampaignCard.jsx` UI updates.
