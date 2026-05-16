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
        Schema::create('google_review_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('status'); // showed, accepted, rejected, dismissed
            $table->boolean('google_redirected')->default(false);
            $table->boolean('feedback_submitted')->default(false);
            $table->foreignId('guest_message_id')->nullable()->constrained('guest_messages')->onDelete('set null');
            $table->timestamp('showed_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_review_interactions');
    }
};
