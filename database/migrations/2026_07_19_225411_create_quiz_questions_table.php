<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->text('text');
            // Null means "use the quiz default", so changing the quiz setting
            // still moves every question that never overrode it.
            $table->unsignedSmallInteger('time_limit_seconds')->nullable();
            $table->unsignedSmallInteger('points')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
