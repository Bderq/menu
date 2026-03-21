<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop old variant tables
        Schema::dropIfExists('store_product_variants');
        Schema::dropIfExists('product_variants');

        // 2. Clean up products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        // 3. Clean up and refine store_products
        Schema::table('store_products', function (Blueprint $table) {
            $table->dropColumn('custom_price');
            $table->integer('sort_order')->default(0)->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('store_products', function (Blueprint $table) {
            $table->decimal('custom_price', 10, 2)->nullable()->after('product_id');
            $table->dropColumn('sort_order');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('image_path');
        });

        // We won't easily restore variant tables in down() but they are not critical for rollback to QR Katalog state.
    }
};
