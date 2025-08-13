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
            // Add composite indexes for performance optimization
            
            // Index for attendance aggregation queries (gender + second service columns)
            $table->index(['attendance_male', 'attendance_female', 'attendance_children'], 'idx_attendance_gender');
            $table->index(['second_service_attendance_male', 'second_service_attendance_female', 'second_service_attendance_children'], 'idx_second_service_attendance');
            
            // Index for guest and convert aggregation
            $table->index(['first_time_guests', 'second_service_first_time_guests'], 'idx_guests');
            $table->index(['converts', 'second_service_converts'], 'idx_converts');
            
            // Composite index for date range + event type queries (most common)
            $table->index(['report_date', 'event_type'], 'idx_date_event_type');
            
            // Index for multi-service reports
            $table->index(['is_multi_service', 'event_type'], 'idx_multi_service_type');
            
            // Index for weekly aggregation (WEEK function optimization)
            $table->index(['report_date', 'event_type', 'is_multi_service'], 'idx_weekly_aggregation');
        });
        
        // Add indexes to small_groups table for membership count optimization
        Schema::table('small_groups', function (Blueprint $table): void {
            if (!Schema::hasColumn('small_groups', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('capacity');
            }
            
            // Composite index for branch + status queries
            $table->index(['branch_id', 'status'], 'idx_branch_status');
        });
        
        // Add indexes to member_small_groups pivot table for membership optimization
        if (Schema::hasTable('member_small_groups')) {
            Schema::table('member_small_groups', function (Blueprint $table): void {
                // Add composite index for small group membership queries
                $table->index(['small_group_id', 'member_id'], 'idx_group_member');
            });
        }
        
        // Add indexes to ministries and departments for leadership optimization
        Schema::table('ministries', function (Blueprint $table): void {
            $table->index(['branch_id', 'leader_id'], 'idx_branch_leader');
        });
        
        Schema::table('departments', function (Blueprint $table): void {
            $table->index(['ministry_id', 'leader_id'], 'idx_ministry_leader');
        });
        
        // Add indexes to member_departments for volunteer count optimization
        if (Schema::hasTable('member_departments')) {
            Schema::table('member_departments', function (Blueprint $table): void {
                $table->index(['department_id', 'member_id'], 'idx_dept_member');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_reports', function (Blueprint $table): void {
            $table->dropIndex('idx_attendance_gender');
            $table->dropIndex('idx_second_service_attendance');
            $table->dropIndex('idx_guests');
            $table->dropIndex('idx_converts');
            $table->dropIndex('idx_date_event_type');
            $table->dropIndex('idx_multi_service_type');
            $table->dropIndex('idx_weekly_aggregation');
        });
        
        Schema::table('small_groups', function (Blueprint $table): void {
            $table->dropIndex('idx_branch_status');
            if (Schema::hasColumn('small_groups', 'status')) {
                $table->dropColumn('status');
            }
        });
        
        if (Schema::hasTable('member_small_groups')) {
            Schema::table('member_small_groups', function (Blueprint $table): void {
                $table->dropIndex('idx_group_member');
            });
        }
        
        Schema::table('ministries', function (Blueprint $table): void {
            $table->dropIndex('idx_branch_leader');
        });
        
        Schema::table('departments', function (Blueprint $table): void {
            $table->dropIndex('idx_ministry_leader');
        });
        
        if (Schema::hasTable('member_departments')) {
            Schema::table('member_departments', function (Blueprint $table): void {
                $table->dropIndex('idx_dept_member');
            });
        }
    }
};
