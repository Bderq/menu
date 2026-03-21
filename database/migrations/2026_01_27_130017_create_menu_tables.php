<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Stores
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // for menu.com/kadikoy
            $table->string('logo_path')->nullable();
            $table->string('theme_color')->default('#ffb000'); // Default gold
            $table->timestamps();
        });

        // 2. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type')->default(\App\Enums\CategoryType::FOOD->value); // food, drink, campaign
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Products (Global Master List)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('price', 10, 2)->nullable(); // Base price if no variants
            $table->string('badge')->nullable(); // e.g., 'New', 'Hot'
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Product Variants (e.g., Sizes: 33cl, 50cl)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });

        // 5. Store Product Overrides (Availability & Metadata)
        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // Overrides (if null, use master product data)
            $table->decimal('custom_price', 10, 2)->nullable(); 
            $table->string('custom_name')->nullable();
            $table->text('custom_description')->nullable();
            $table->string('custom_image_path')->nullable();
            $table->boolean('is_active')->default(true); // Can hide a global product in specific store
            $table->boolean('is_featured')->default(false); 
            
            $table->timestamps();

            $table->unique(['store_id', 'product_id']);
        });

        // 6. Store Variant Prices (Price Overrides for Variants)
        Schema::create('store_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            
            $table->decimal('price', 10, 2); // The store-specific price
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            
            $table->unique(['store_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_product_variants');
        Schema::dropIfExists('store_products');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('stores');
    }
};
