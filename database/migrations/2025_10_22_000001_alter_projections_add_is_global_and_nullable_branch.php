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
        Schema::table('projections', function (Blueprint $table): void {
            // Make branch_id nullable to allow global projections
            $table->foreignId('branch_id')->nullable()->change();

            // Add is_global flag (default false)
            $table->boolean('is_global')->default(false)->after('branch_id')->index();

            // Drop existing unique and recreate composite unique including is_global
            $table->dropUnique(['branch_id', 'year']);
            $table->unique(['year', 'is_global', 'branch_id'], 'projections_year_scope_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projections', function (Blueprint $table): void {
            // Revert unique back
            $table->dropUnique('projections_year_scope_unique');
            $table->unique(['branch_id', 'year']);

            // Drop is_global
            $table->dropColumn('is_global');

            // Make branch_id not nullable again
            $table->foreignId('branch_id')->nullable(false)->change();
        });
    }
};


