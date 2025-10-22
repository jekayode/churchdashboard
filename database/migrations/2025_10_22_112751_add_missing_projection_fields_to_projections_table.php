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
            // Add missing projection fields
            $table->integer('weekly_avg_attendance_target')->nullable()->after('attendance_target');
            $table->integer('guests_target')->nullable()->after('weekly_avg_attendance_target');
            $table->integer('weekly_avg_guests_target')->nullable()->after('guests_target');
            $table->integer('weekly_avg_converts_target')->nullable()->after('converts_target');
            $table->integer('lifegroups_target')->nullable()->after('volunteers_target');
            $table->integer('lifegroups_memberships_target')->nullable()->after('lifegroups_target');
            $table->integer('lifegroups_weekly_avg_attendance_target')->nullable()->after('lifegroups_memberships_target');

            // Add quarterly fields for new metrics
            $table->json('quarterly_weekly_avg_attendance')->nullable()->after('quarterly_attendance');
            $table->json('quarterly_guests')->nullable()->after('quarterly_weekly_avg_attendance');
            $table->json('quarterly_weekly_avg_guests')->nullable()->after('quarterly_guests');
            $table->json('quarterly_weekly_avg_converts')->nullable()->after('quarterly_converts');
            $table->json('quarterly_lifegroups')->nullable()->after('quarterly_leaders');
            $table->json('quarterly_lifegroups_memberships')->nullable()->after('quarterly_lifegroups');
            $table->json('quarterly_lifegroups_weekly_avg_attendance')->nullable()->after('quarterly_lifegroups_memberships');

            // Add quarterly actual fields for new metrics
            $table->json('quarterly_actual_weekly_avg_attendance')->nullable()->after('quarterly_actual_attendance');
            $table->json('quarterly_actual_guests')->nullable()->after('quarterly_actual_weekly_avg_attendance');
            $table->json('quarterly_actual_weekly_avg_guests')->nullable()->after('quarterly_actual_guests');
            $table->json('quarterly_actual_weekly_avg_converts')->nullable()->after('quarterly_actual_converts');
            $table->json('quarterly_actual_lifegroups')->nullable()->after('quarterly_actual_leaders');
            $table->json('quarterly_actual_lifegroups_memberships')->nullable()->after('quarterly_actual_lifegroups');
            $table->json('quarterly_actual_lifegroups_weekly_avg_attendance')->nullable()->after('quarterly_actual_lifegroups_memberships');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projections', function (Blueprint $table): void {
            $table->dropColumn([
                'weekly_avg_attendance_target',
                'guests_target',
                'weekly_avg_guests_target',
                'weekly_avg_converts_target',
                'lifegroups_target',
                'lifegroups_memberships_target',
                'lifegroups_weekly_avg_attendance_target',
                'quarterly_weekly_avg_attendance',
                'quarterly_guests',
                'quarterly_weekly_avg_guests',
                'quarterly_weekly_avg_converts',
                'quarterly_lifegroups',
                'quarterly_lifegroups_memberships',
                'quarterly_lifegroups_weekly_avg_attendance',
                'quarterly_actual_weekly_avg_attendance',
                'quarterly_actual_guests',
                'quarterly_actual_weekly_avg_guests',
                'quarterly_actual_weekly_avg_converts',
                'quarterly_actual_lifegroups',
                'quarterly_actual_lifegroups_memberships',
                'quarterly_actual_lifegroups_weekly_avg_attendance',
            ]);
        });
    }
};
