<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_plan_id')->constrained()->cascadeOnDelete();

            $table->unsignedSmallInteger('day_number');
            // "0717" for annual plans, so the same content serves every year.
            $table->char('month_day', 4)->nullable();
            $table->string('label')->nullable();

            // Reference-led days. Groups are kept apart so the app can show
            // "Old Testament / New Testament / Psalm / Proverbs" sections.
            $table->string('old_testament')->nullable();
            $table->string('new_testament')->nullable();
            $table->string('psalm')->nullable();
            $table->string('proverbs')->nullable();
            $table->json('passages')->nullable();

            // Devotional days.
            $table->string('title')->nullable();
            $table->string('focus_verse')->nullable();
            $table->longText('body')->nullable();
            $table->text('reflection_prompt')->nullable();

            // "What Now?" study questions (the plan ships up to two).
            $table->text('study_question_1')->nullable();
            $table->text('study_question_2')->nullable();

            $table->string('source_url')->nullable();
            $table->timestamps();

            $table->unique(['reading_plan_id', 'day_number']);
            $table->index(['reading_plan_id', 'month_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_days');
    }
};
