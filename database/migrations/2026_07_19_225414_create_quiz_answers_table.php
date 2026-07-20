<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_participant_id')->constrained()->cascadeOnDelete();
            // Null records a question that ran out of time unanswered.
            $table->foreignId('quiz_option_id')->nullable()->constrained()->nullOnDelete();

            // Measured on the server, from when the question was due to start to
            // when the answer arrived. A client-reported time would make winning
            // a matter of editing one number.
            $table->unsignedInteger('response_ms');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('points_awarded')->default(0);
            $table->timestamps();

            // The real guard against double answers. Hiding the button after a
            // tap only discourages it; two taps in the same instant, a retried
            // request or a rejoin would all get through without this.
            $table->unique(['quiz_question_id', 'quiz_participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
