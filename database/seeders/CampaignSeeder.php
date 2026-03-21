<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProductPortion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Clear existing data to avoid duplicates
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \DB::table('campaign_store')->truncate();
        \DB::table('campaign_items')->truncate();
        \DB::table('campaigns')->truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $stores = Store::all();
        
        // Ensure we have the "Kampanyalar" category for the gallery if needed, 
        // but the MenuController also fetches from Campaign model directly.
        Category::firstOrCreate(
            ['slug' => 'kampanyalar'],
            ['name' => 'Kampanyalar', 'type' => \App\Enums\CategoryType::CAMPAIGN->value, 'sort_order' => -10]
        );

        // 1. FIXED PRICE CAMPAIGN
        // Check for existing image
        $campSlug = Str::slug('Günün Kokteyli: Kuzu Tonik');
        $campImage = Storage::disk('public')->exists("campaigns/{$campSlug}.webp") ? "campaigns/{$campSlug}.webp" : null;

        $fixedCamp = \App\Models\Campaign::create([
            'name' => 'Günün Kokteyli: Kuzu Tonik',
            'display_title' => 'GÜNÜN KOKTEYLİ',
            'description' => 'Beefeater bazlı imza kokteylimiz bugün özel fiyata!',
            'image_path' => $campImage,
            'type' => \App\Enums\CampaignType::FIXED_PRICE->value,
            'value' => 280, // Target price
            'priority' => 10,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
        ]);

        $kuzuTonik = Product::where('name', 'Kuzu Tonik')->first();
        if ($kuzuTonik) {
            \App\Models\CampaignItem::create([
                'campaign_id' => $fixedCamp->id,
                'product_id' => $kuzuTonik->id,
                'price_override' => 280,
            ]);
        }

        // 2. PERCENTAGE DISCOUNT CAMPAIGN
        $percCamp = \App\Models\Campaign::create([
            'name' => 'Pizza Günü İndirimi',
            'display_title' => '%20 PİZZA İNDİRİMİ',
            'description' => 'Tüm gurme pizzalarda geçerli sürpriz indirim!',
            'type' => \App\Enums\CampaignType::PERCENTAGE->value,
            'value' => 20, // 20% off
            'priority' => 5,
            'is_active' => true,
        ]);

        $pizzas = Product::whereHas('category', function($q) {
            $q->where('slug', 'gurme-pizzalar');
        })->get();

        foreach ($pizzas as $pizza) {
            \App\Models\CampaignItem::create([
                'campaign_id' => $percCamp->id,
                'product_id' => $pizza->id,
            ]);
        }

        // 3. BUNDLE / SPECIAL PRICE CAMPAIGN
        $bundleCamp = \App\Models\Campaign::create([
            'name' => 'Bira Tabağı Fırsatı',
            'display_title' => 'PAYLAŞIM FIRSATI',
            'description' => 'Bira Tabağı şimdi çok daha avantajlı!',
            'type' => \App\Enums\CampaignType::BUNDLE->value,
            'value' => 380, // Bundle price
            'priority' => 15,
            'is_active' => true,
        ]);

        $tabs = Product::where('name', 'Bira Tabağı')->first();
        if ($tabs) {
            \App\Models\CampaignItem::create([
                'campaign_id' => $bundleCamp->id,
                'product_id' => $tabs->id,
            ]);
        }

        // 4. X GET Y CAMPAIGN
        $xgetyCamp = \App\Models\Campaign::create([
            'name' => 'Shot Partisi: 4 Al 3 Öde',
            'display_title' => '4 AL 3 ÖDE',
            'description' => 'Tüm Winx shotlarda geçerli party menüsü!',
            'type' => \App\Enums\CampaignType::X_GET_Y->value,
            'buy_qty' => 4,
            'get_qty' => 3,
            'priority' => 20,
            'is_active' => true,
        ]);

        $winx = Product::where('name', 'Winx')->first();
        if ($winx) {
            \App\Models\CampaignItem::create([
                'campaign_id' => $xgetyCamp->id,
                'product_id' => $winx->id,
            ]);
        }

        // Link all campaigns to all stores
        $allCampaigns = \App\Models\Campaign::all();
        foreach ($allCampaigns as $campaign) {
            foreach ($stores as $store) {
                $campaign->stores()->attach($store->id, ['is_active' => true]);
            }
        }
    }
}
