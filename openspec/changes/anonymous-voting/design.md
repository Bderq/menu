# Design: Anonymous Product Voting

## Overview
This feature allows users to "like" or "vote" for products without a login. It leverages the `visitor_id` and fingerprinting system to ensure a "one-vote-per-device" constraint.

## Architecture

### Data Layer
- **Table**: `votes`
- **Columns**:
    - `id`: Primary Key
    - `visitor_id`: Foreign Key to `visitors.id`
    - `product_id`: Foreign Key to `products.id`
    - `created_at`, `updated_at`
- **Index**: Unique index on `[visitor_id, product_id]` to prevent duplicate votes.

### API Layer
- **Endpoint**: `POST /tracking/vote`
- **Payload**: `{ product_id: int }`
- **Logic**: 
    1. Resolve `visitor_id` from middleware.
    2. Check if a vote already exists for this pair.
    3. If exists: Delete (Toggle Off).
    4. If not exists: Create (Toggle On).

### Frontend Layer
- **Component**: `ProductCard` (within `Index.jsx`)
- **State**: Needs to know if the current user has liked the product.
- **Hydration**: The initial `menuData` or a separate call must provide the current visitor's liked product IDs.

## Security & Integrity
- Uses the `TrackVisitor` middleware to ensure every request has an identity.
- Uses Database-level unique constraints for absolute integrity.
- Fingerprint recovery ensures that incognito users can't vote twice if their identity is merged.
