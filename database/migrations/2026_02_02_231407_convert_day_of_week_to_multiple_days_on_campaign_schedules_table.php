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
        Schema::table('campaign_schedules', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
            $table->json('days')->after('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_schedules', function (Blueprint $table) {
            $table->dropColumn('days');
            $table->string('day_of_week')->after('campaign_id')->nullable();
        });
    }
};
