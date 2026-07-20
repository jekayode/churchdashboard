<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('description')->nullable();

            // Typed in by the congregation from the projector, so it is drawn
            // from an alphabet with no 0/O or 1/I/L. Unique among live quizzes.
            $table->string('code', 8)->nullable()->unique();

            $table->enum('status', ['draft', 'lobby', 'running', 'finished'])->default('draft');

            // Defaults a question inherits unless it overrides them.
            $table->unsignedSmallInteger('seconds_per_question')->default(20);
            $table->unsignedSmallInteger('base_points')->default(1000);
            // How long the answer and standings stay up before the next question.
            $table->unsignedSmallInteger('reveal_seconds')->default(6);

            $table->boolean('allow_guests')->default(true);

            /*
             * The whole run is derived from started_at: question N begins at
             * started_at + the durations before it. That is what lets a phone
             * that locked, lost signal or joined late work out the current
             * question and its remaining time from a single fetch, instead of
             * depending on having heard an earlier event.
             *
             * Pausing would break that arithmetic, so paused time is banked in
             * paused_ms and subtracted from elapsed rather than moving started_at.
             */
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->unsignedInteger('paused_ms')->default(0);
            $table->timestamp('finished_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
