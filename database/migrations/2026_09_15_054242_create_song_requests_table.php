<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_message_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('artist')->nullable();
            $table->string('title')->nullable();
            $table->json('candidates');
            $table->string('status')->default('pending');
            $table->string('chosen_track_uri')->nullable();
            $table->string('chosen_track_name')->nullable();
            $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->string('queued_by')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_requests');
    }
};
