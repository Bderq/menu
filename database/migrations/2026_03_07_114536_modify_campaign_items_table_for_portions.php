<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaign_items', function (Blueprint $table) {
            $table->foreignId('store_product_portion_id')->nullable()->constrained('store_product_portions')->cascadeOnDelete();
        });

        // Migrate existing data
        $items = DB::table('campaign_items')->whereNotNull('portion_name')->get();
        foreach ($items as $item) {
            $portion = DB::table('store_product_portions')
                ->where('product_id', $item->product_id)
                ->where('name', $item->portion_name)
                ->first();

            if ($portion) {
                DB::table('campaign_items')
                    ->where('id', $item->id)
                    ->update(['store_product_portion_id' => $portion->id]);
            }
        }

        Schema::table('campaign_items', function (Blueprint $table) {
            $table->dropColumn('portion_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_items', function (Blueprint $table) {
            $table->string('portion_name')->nullable();
        });

        // Reverse mapping
        $items = DB::table('campaign_items')->whereNotNull('store_product_portion_id')->get();
        foreach ($items as $item) {
            $portion = DB::table('store_product_portions')
                ->where('id', $item->store_product_portion_id)
                ->first();
                
            if ($portion) {
                DB::table('campaign_items')
                    ->where('id', $item->id)
                    ->update(['portion_name' => $portion->name]);
            }
        }

        Schema::table('campaign_items', function (Blueprint $table) {
            $table->dropForeign(['store_product_portion_id']);
            $table->dropColumn('store_product_portion_id');
        });
    }
};
