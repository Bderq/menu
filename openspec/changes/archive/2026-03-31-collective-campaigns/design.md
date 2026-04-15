## Context

The venue has multiple volume-based discounts (e.g., Efes 4-pack, 8-pack, 12-pack). Currently, each packet must be created as a separate bundle campaign, which clutters the frontend campaign gallery and makes management tedious. A tiered pricing structure is needed so a single "Collective" campaign can display a matrix of quantity/price options.

## Goals / Non-Goals

**Goals:**
- Add a new campaign type `CampaignType::COLLECTIVE`.
- Store tier data (quantity and price) dynamically in the `campaigns` table.
- Provide a simple admin UI in Filament to manage these tiers.
- Render tiered pricing lists gracefully on the frontend.

**Non-Goals:**
- Shopping cart functionality. (This is a view-only QR menu, so the collective campaign simply acts as a pricing informational tool for customers to place orders with the waitstaff).
- Tiered discounts for mixed products (e.g., 2 Efes + 2 Becks under the same tiered price logic). This is strictly for homogenous line items or items bundled under a single campaign relationship.

## Decisions

**1. Data Storage for Tiers**
- *Decision*: Add a `tiers` JSON column to the `campaigns` table.
- *Rationale*: Tiers belong strictly to campaigns. Creating a separate `campaign_tiers` table is over-engineering for a simple `[{"qty":4, "price":580}]` structure. JSON allows flexibility and seamlessly integrates with Filament's Repeater component.

**2. Frontend Rendering Strategy**
- *Decision*: Pass `collective_tiers` array inside the product's formatted JSON. Redesign the campaign card drawer to render a table/list from this array.
- *Rationale*: Instead of replacing `campaign_price` with a single value, the presence of `collective_tiers` acts as a flag for the frontend to switch from a "Single Price Block" layout to a "Multi-Tier List" layout.

## Risks / Trade-offs

- **Risk:** Existing campaigns might fail if the `tiers` column missing in queries.
  *Mitigation:* Create the column as `nullable` and cast it to `array` in the model.
- **Risk:** The Filament `value` and `buy_qty` columns might be irrelevant for `COLLECTIVE` campaigns.
  *Mitigation:* Use reactive Filament forms (`hidden(fn (Get $get) => $get('type') === CampaignType::COLLECTIVE->value)`) to hide unnecessary fields and show the `tiers` Repeater only when applicable.
