<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // "passages" = reference-led (Bible in a Year); "devotional" = written
            // day with a focus verse and reflection prompt.
            $table->enum('type', ['passages', 'devotional'])->default('passages');

            // Annual plans repeat every year and are keyed by month-day rather
            // than a fixed calendar date; finite plans run day 1..length.
            $table->boolean('is_annual')->default(false);
            $table->unsignedSmallInteger('length_days')->default(0);

            $table->enum('tone', ['orange', 'purple', 'amber', 'lemon'])->default('orange');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('attribution')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_plans');
    }
};
