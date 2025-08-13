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
        Schema::table('event_reports', function (Blueprint $table): void {
            // Add service_type column for more granular service categorization
            $table->string('service_type')->nullable()->after('event_type');
            
            // Add index for better query performance
            $table->index('service_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_reports', function (Blueprint $table): void {
            $table->dropIndex(['service_type']);
            $table->dropColumn('service_type');
        });
    }
};
