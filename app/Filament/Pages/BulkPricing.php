<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Store;
use App\Models\StoreProductPortion;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class BulkPricing extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected string $view = 'filament.pages.bulk-pricing';
    protected static ?string $title = 'Bulk Pricing Manager';

    public function getViewData(): array
    {
        return [
            'gridData' => $this->getGridData(),
            'storeColumns' => $this->getStoreColumns(),
        ];
    }

    public function getStoreColumns()
    {
        return Store::orderBy('name')->get(['id', 'name', 'slug']);
    }

    public function getGridData()
    {
        // 1. Fetch Keys
        $products = \App\Models\Product::with(['category'])->orderBy('category_id')->orderBy('name')->get();
        $stores = Store::all();
        
        // Fetch existing portions to map prices
        $existingPortions = StoreProductPortion::all();
        
        // Map: [product_id][portion_name][store_id] = { price, id }
        $priceMap = [];
        foreach ($existingPortions as $p) {
            $priceMap[$p->product_id][$p->name][$p->store_id] = [
                'price' => (float)$p->price,
                'id' => $p->id
            ];
        }

        $gridRows = [];

        foreach ($products as $product) {
            // Determine the "rows" for this product based on existing data
            // If the product has ANY prices/portions defined anywhere, use those names.
            // If it has absolutely nothing (clean slate), show 1 "Standart" row.
            
            $productPortionNames = isset($priceMap[$product->id]) 
                ? array_keys($priceMap[$product->id]) 
                : [];
            
            // If no portions exist for this product at all, create a default "Standart" option
            if (empty($productPortionNames)) {
                $productPortionNames = ['Standart'];
            }

            foreach ($productPortionNames as $portionName) {
                // Ensure portionName isn't empty (sanity check)
                $pName = $portionName ?: 'Standart';

                $storeData = $priceMap[$product->id][$pName] ?? [];

                $row = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'category_name' => $product->category->name ?? 'Diğer',
                    'portion_name' => $pName,
                    'is_unique_row' => "{$product->id}_{$pName}"
                ];

                // Add columns for each store
                foreach ($stores as $store) {
                    $hasData = isset($storeData[$store->id]);
                    // If hasData is true, we have a price (even if 0). 
                    // If false, it's null (User sees empty cell)
                    $row["store_{$store->id}"] = $hasData ? $storeData[$store->id]['price'] : null;
                    $row["store_{$store->id}_id"] = $hasData ? $storeData[$store->id]['id'] : null;
                }

                $gridRows[] = $row;
            }
        }

        return $gridRows;
    }

    public function batchUpdate(array $changes)
    {
        $count = 0;

        foreach ($changes as $change) {
            $field = $change['field']; // "store_1", "store_2" etc.
            $newValue = $change['newValue'];

            if (!str_starts_with($field, 'store_')) continue;

            $storeId = str_replace('store_', '', $field);
            // $rowDataId is composite here passed from the grid row logic, we need to ensure we have product_id and portion_name
            // But wait! Alpine sends "row_id" from the row object. 
            // In getGridData, 'is_unique_row' = "{$product->id}_{$portionName}"
            
            // To make it robust, we need to pass identifying info in the row data.
            // Let's rely on hidden columns or parsing the ID if needed.
            // Actually, cleanest is to trust the grid passes the row data.
            // But here we only get the change object constructed in Alpine.
            // Alpine constructs: { row_id: params.data.is_unique_row, field: ..., newValue: ... }
            
            // Wait, we need product_id and portion_name to CREATE a record if it doesn't exist.
            // The "row_id" string contains "productId_portionName".
            
            // Problem: Portion Name can contain underscores? Yes. 
            // Better strategy: Store product_id and portion_name in the row, send them in the changes payload from JS.
            
            // Let's assume JS sends: { product_id: 5, portion_name: 'Standart', store_id: 1, value: 50.00 }
            // So we need to update the Blade JS logic too.
            
            // For now, let's implement the PHP assuming we get the right keys
            $productId = $change['product_id'];
            $portionName = $change['portion_name'];
            $storeId = $change['store_id']; 
            
            // Special Case: New Value is empty/null -> DELETE record (Passive)
            if ($newValue === '' || $newValue === null) {
                \App\Models\StoreProductPortion::where([
                    'store_id' => $storeId,
                    'product_id' => $productId,
                    'name' => $portionName
                ])->delete();
                
                $count++;
                continue;
            }

            // Update or Create (Even if value is 0)
            \App\Models\StoreProductPortion::updateOrCreate(
                [
                    'store_id' => $storeId,
                    'product_id' => $productId,
                    'name' => $portionName
                ],
                [
                    'price' => $newValue,
                    'is_active' => true
                ]
            );

            // Ensure Product is attached to Store (store_products pivot)
            // We use syncWithoutDetaching to avoid errors if already attached
            $store = \App\Models\Store::find($storeId);
            if ($store) {
                 // Check if attached first to avoid extra queries? syncWithoutDetaching does this well.
                 // We also set is_active = true on the pivot if we are enabling a price
                 $store->products()->syncWithoutDetaching([
                     $productId => ['is_active' => true]
                 ]);
                 
                 // Note: If using multiple portions, we don't want to detach the product if one portion is deleted.
                 // So only ATTACH/UPDATE here.
            }
            
            $count++;
        }
        
        Notification::make()
            ->title("Saved {$count} updates")
            ->success()
            ->send();
    }
}
