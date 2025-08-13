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
            // Multi-service support fields
            $table->boolean('is_multi_service')->default(false)->after('number_of_cars');
            
            // Second service attendance data
            $table->integer('second_service_attendance_male')->default(0)->after('is_multi_service');
            $table->integer('second_service_attendance_female')->default(0)->after('second_service_attendance_male');
            $table->integer('second_service_attendance_children')->default(0)->after('second_service_attendance_female');
            $table->integer('second_service_first_time_guests')->default(0)->after('second_service_attendance_children');
            $table->integer('second_service_converts')->default(0)->after('second_service_first_time_guests');
            $table->integer('second_service_number_of_cars')->default(0)->after('second_service_converts');
            
            // Second service times
            $table->dateTime('second_service_start_time')->nullable()->after('second_service_number_of_cars');
            $table->dateTime('second_service_end_time')->nullable()->after('second_service_start_time');
            
            // Additional metadata
            $table->string('event_type')->nullable()->after('second_service_end_time');
            $table->text('second_service_notes')->nullable()->after('event_type');
            
            // Indexes for better query performance
            $table->index('is_multi_service');
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_reports', function (Blueprint $table): void {
            $table->dropIndex(['is_multi_service']);
            $table->dropIndex(['event_type']);
            
            $table->dropColumn([
                'is_multi_service',
                'second_service_attendance_male',
                'second_service_attendance_female', 
                'second_service_attendance_children',
                'second_service_first_time_guests',
                'second_service_converts',
                'second_service_number_of_cars',
                'second_service_start_time',
                'second_service_end_time',
                'event_type',
                'second_service_notes'
            ]);
        });
    }
};
