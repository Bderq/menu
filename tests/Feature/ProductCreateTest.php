<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProductPortion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product_and_save_portions_with_correct_keys()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a store
        $store = Store::create([
            'name' => 'Görükle',
            'slug' => 'gorukle',
            'theme_color' => '#ffb000',
        ]);

        $category = Category::create([
            'name' => 'Burgers',
            'slug' => 'burgers',
        ]);

        // Simulating the way Filament Repeater sends data (sometimes with UUID keys)
        $portionsData = [
            'uuid-1' => ['name' => 'Small', 'price' => 100],
            'uuid-2' => ['name' => 'Large', 'price' => 150],
        ];

        Livewire::test(CreateProduct::class)
            // Fill basic info
            ->fillForm([
                'name' => 'Cheeseburger',
                'category_id' => $category->id,
                'description' => 'Delicious cheeeese',
                'is_active' => true,
                'badges' => [], 
                
                // Fill Store Specific Info with UUID keys simulating repeater
                "store_{$store->id}_active" => true,
                "store_{$store->id}_portions" => $portionsData,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Check Portions Saved Correctly
        $product = Product::first();
        
        $portions = StoreProductPortion::where('product_id', $product->id)
            ->where('store_id', $store->id)
            ->orderBy('sort_order')
            ->get();

        $this->assertCount(2, $portions);
        // Verify indexes are 0 and 1, not UUID strings (this would have failed before fix)
        $this->assertEquals(0, $portions[0]->sort_order);
        $this->assertEquals(1, $portions[1]->sort_order);
        
        $this->assertEquals('Small', $portions[0]->name);
        $this->assertEquals('Large', $portions[1]->name);
    }
}
