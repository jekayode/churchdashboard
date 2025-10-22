<?php

declare(strict_types=1);

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
        Schema::create('guest_prayer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_prayer_request_id')->constrained('guest_prayer_requests')->onDelete('cascade');
            $table->foreignId('prayed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('prayed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['guest_prayer_request_id', 'prayed_at']);
            $table->index(['prayed_by', 'prayed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_prayer_logs');
    }
};
