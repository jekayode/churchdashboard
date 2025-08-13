<?php

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
        Schema::table('projections', function (Blueprint $table) {
            // Add quarterly breakdown fields for more detailed planning
            $table->json('quarterly_attendance')->nullable()->after('quarterly_breakdown');
            $table->json('quarterly_converts')->nullable()->after('quarterly_attendance');
            $table->json('quarterly_leaders')->nullable()->after('quarterly_converts');
            $table->json('quarterly_volunteers')->nullable()->after('quarterly_leaders');
            
            // Add quarterly progress tracking
            $table->json('quarterly_actual_attendance')->nullable()->after('quarterly_volunteers');
            $table->json('quarterly_actual_converts')->nullable()->after('quarterly_actual_attendance');
            $table->json('quarterly_actual_leaders')->nullable()->after('quarterly_actual_converts');
            $table->json('quarterly_actual_volunteers')->nullable()->after('quarterly_actual_leaders');
            
            // Add indexes for performance
            $table->index(['branch_id', 'year', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projections', function (Blueprint $table) {
            $table->dropColumn([
                'quarterly_attendance',
                'quarterly_converts', 
                'quarterly_leaders',
                'quarterly_volunteers',
                'quarterly_actual_attendance',
                'quarterly_actual_converts',
                'quarterly_actual_leaders',
                'quarterly_actual_volunteers'
            ]);
            
            $table->dropIndex(['branch_id', 'year', 'deleted_at']);
        });
    }
};
