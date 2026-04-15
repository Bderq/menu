## 1. Database and Model Updates

- [x] 1.1 Create migration to add JSON `tiers` column to `campaigns` table
- [x] 1.2 Update `App\Models\Campaign` to cast `tiers` as `array`
- [x] 1.3 Add `COLLECTIVE = 'collective'` to `App\Enums\CampaignType` enum

## 2. Filament Admin Panel Updates

- [x] 2.1 Update `CampaignResource` form to conditionally hide `value` and `buy_qty` fields when type is `COLLECTIVE`
- [x] 2.2 Add a Repeater field named `tiers` in `CampaignResource` to handle quantity and price input when type is `COLLECTIVE`

## 3. Backend Services Logic

- [x] 3.1 Modify `CampaignService::applyDiscountToOption` and `CampaignService::applyDiscountToProduct` to process `COLLECTIVE` type campaigns
- [x] 3.2 Ensure the lowest price from `tiers` is calculated and assigned as `campaign_price` (starting price) 
- [x] 3.3 Ensure the `tiers` array is attached to the product/option array as `collective_tiers` so the frontend can receive it

## 4. Frontend Rendering Updates

- [x] 4.1 Update `DefaultCampaignCard.jsx` to render a list/table format of the `collective_tiers` array instead of standard discounted styling when the campaign is `COLLECTIVE`
- [x] 4.2 Update `Index.jsx` product details drawer to render the pricing matrix if `collective_tiers` is present
- [x] 4.3 Ensure `npm run build` is executed to apply frontend changes
