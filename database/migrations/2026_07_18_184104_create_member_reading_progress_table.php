<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_plan_id')->constrained()->cascadeOnDelete();

            // The member's local date the reading was completed on. Streaks are
            // counted from this, so they behave correctly across timezones.
            $table->date('completed_on');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['member_id', 'reading_day_id']);
            $table->index(['member_id', 'completed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_reading_progress');
    }
};
