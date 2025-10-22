<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip enum modification for SQLite compatibility
        // The enum values are already correct in the create_members_table migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip enum modification for SQLite compatibility
    }
};
