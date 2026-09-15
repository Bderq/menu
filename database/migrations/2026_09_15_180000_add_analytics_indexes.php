<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->index(['visitor_id', 'started_at'], 'visits_visitor_started_idx'); // TrackVisitor session lookup
            $table->index('started_at', 'visits_started_at_idx');                      // range stats, prune
            $table->index(['store_id', 'started_at'], 'visits_store_started_idx');     // store-filtered reports
            $table->index('table_id', 'visits_table_id_idx');                          // TopTablesTable
        });

        Schema::table('interactions', function (Blueprint $table) {
            $table->index('visit_id', 'interactions_visit_id_idx');                    // joins + cascade delete
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->index('last_seen_at', 'visitors_last_seen_at_idx');
            $table->index('created_at', 'visitors_created_at_idx');
        });

        Schema::table('google_review_interactions', function (Blueprint $table) {
            $table->index(['store_id', 'showed_at'], 'gri_store_showed_idx');
            $table->index('visitor_id', 'gri_visitor_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_visitor_started_idx');
            $table->dropIndex('visits_started_at_idx');
            $table->dropIndex('visits_store_started_idx');
            $table->dropIndex('visits_table_id_idx');
        });

        Schema::table('interactions', function (Blueprint $table) {
            $table->dropIndex('interactions_visit_id_idx');
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex('visitors_last_seen_at_idx');
            $table->dropIndex('visitors_created_at_idx');
        });

        Schema::table('google_review_interactions', function (Blueprint $table) {
            $table->dropIndex('gri_store_showed_idx');
            $table->dropIndex('gri_visitor_id_idx');
        });
    }
};
