<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Add missing columns only if they don't exist
            if (! Schema::hasColumn('events', 'type')) {
                $table->string('type')->default('other')->after('description');
            }
            if (! Schema::hasColumn('events', 'max_capacity')) {
                $table->integer('max_capacity')->nullable()->after('end_date');
            }
            if (! Schema::hasColumn('events', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('status');
            }
        });

        // Map existing status values to new ones
        DB::table('events')->where('status', 'published')->update(['status' => 'active']);
        DB::table('events')->where('status', 'draft')->update(['status' => 'active']);

        // Map existing registration_type values to new ones
        DB::table('events')->where('registration_type', 'custom_form')->update(['registration_type' => 'form']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Remove added columns if they exist
            if (Schema::hasColumn('events', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('events', 'max_capacity')) {
                $table->dropColumn('max_capacity');
            }
            if (Schema::hasColumn('events', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });

        // Revert status mappings
        DB::table('events')->where('status', 'active')->update(['status' => 'published']);
        DB::table('events')->where('registration_type', 'form')->update(['registration_type' => 'custom_form']);
    }
};
