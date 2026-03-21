<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Main Campaign Table
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Internal name
            $table->string('display_title'); // "HAPPY HOUR"
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            
            // Logic Fields
            $table->string('type'); // 'bundle', 'x_get_y', 'percentage', 'fixed_price'
            $table->decimal('value', 10, 2)->nullable(); // 25.00 (%), 50.00 (Fixed), 450.00 (Bundle Price)
            
            // X Get Y Fields
            $table->integer('buy_qty')->nullable();
            $table->integer('get_qty')->nullable();
            
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Schedule Table (When is it active?)
        Schema::create('campaign_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week'); // monday, tuesday...
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        // 3. Campaign Items (What products are included?)
        Schema::create('campaign_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            
            // We link to specific PORTIONS (via product + portion name logic)
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('portion_name')->nullable(); // "50cl", "Tam Boy" - If null, applies to whole product
            
            $table->decimal('price_override', 10, 2)->nullable(); // Specific price for this item in this campaign
            $table->boolean('is_optional')->default(false); // For Bundles: Can I choose this or that?
            
            $table->timestamps();
        });

        // 4. Store Association (Where is it active?)
        Schema::create('campaign_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_store');
        Schema::dropIfExists('campaign_items');
        Schema::dropIfExists('campaign_schedules');
        Schema::dropIfExists('campaigns');
    }
};
