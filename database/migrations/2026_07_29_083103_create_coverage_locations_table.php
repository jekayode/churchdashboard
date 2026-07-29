<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coverage_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            // The "closest location" a member picks — the areas a branch's
            // LifeGroups cover. Managed per branch, because coverage differs.
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            // Retired areas stay in the table so a member who chose one keeps a
            // meaningful value; they are just not offered to new people.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'name']);
            $table->index(['branch_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coverage_locations');
    }
};
