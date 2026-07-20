<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('text', 120);
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['quiz_question_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
    }
};
