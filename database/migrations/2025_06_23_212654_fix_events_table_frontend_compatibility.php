<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Add missing columns only if they don't exist
            if (!Schema::hasColumn('events', 'type')) {
                $table->string('type')->default('other')->after('description');
            }
            if (!Schema::hasColumn('events', 'max_capacity')) {
                $table->integer('max_capacity')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('events', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('status');
            }
        });

        // First expand enum values to include both old and new values
        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'published', 'cancelled', 'completed', 'active') DEFAULT 'active'");
        DB::statement("ALTER TABLE events MODIFY COLUMN registration_type ENUM('link', 'custom_form', 'none', 'simple', 'form') DEFAULT 'simple'");

        // Map existing status values to new ones
        DB::table('events')->where('status', 'published')->update(['status' => 'active']);
        DB::table('events')->where('status', 'draft')->update(['status' => 'active']);

        // Map existing registration_type values to new ones
        DB::table('events')->where('registration_type', 'custom_form')->update(['registration_type' => 'form']);

        // Finally update enum values to only include new values
        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('active', 'completed', 'cancelled') DEFAULT 'active'");
        DB::statement("ALTER TABLE events MODIFY COLUMN registration_type ENUM('none', 'simple', 'form', 'link') DEFAULT 'simple'");
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

        // Revert enum values for status
        DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft'");
        
        // Revert enum values for registration_type  
        DB::statement("ALTER TABLE events MODIFY COLUMN registration_type ENUM('link', 'custom_form') DEFAULT 'custom_form'");
        
        // Revert status mappings
        DB::table('events')->where('status', 'active')->update(['status' => 'published']);
        DB::table('events')->where('registration_type', 'form')->update(['registration_type' => 'custom_form']);
    }
};
