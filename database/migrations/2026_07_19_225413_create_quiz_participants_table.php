<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();

            // Null for a guest. A guest is identified by a token held on their
            // device, which is also what lets them claim this score if they
            // sign up afterwards — without it, "sign in to keep your score" is
            // an empty promise, because the score is already unreachable.
            $table->foreignId('member_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('guest_token', 64)->nullable();

            $table->string('display_name', 24);

            /*
             * Both are derived from quiz_answers and never edited directly, so a
             * disputed result can always be explained from the answers.
             * total_response_ms is the tie-break: equal scores are separated by
             * who answered faster overall, so there is always one winner.
             */
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total_response_ms')->default(0);
            $table->unsignedSmallInteger('correct_count')->default(0);

            $table->timestamp('joined_at')->useCurrent();
            // Set when the host removes someone, rather than deleting them, so
            // their answers stay in the audit trail.
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();

            // One membership per quiz. MySQL permits repeated NULLs, so this
            // constrains members without preventing many guests.
            $table->unique(['quiz_id', 'member_id']);
            $table->index(['quiz_id', 'guest_token']);
            // Serves the leaderboard read, which is the hottest query in a run.
            $table->index(['quiz_id', 'score', 'total_response_ms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_participants');
    }
};
