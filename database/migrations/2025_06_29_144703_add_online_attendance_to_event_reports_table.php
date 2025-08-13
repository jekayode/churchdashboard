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
            // Add online attendance for first service
            $table->integer('attendance_online')->default(0)->after('attendance_children');
            
            // Add online attendance for second service
            $table->integer('second_service_attendance_online')->default(0)->after('second_service_attendance_children');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_reports', function (Blueprint $table): void {
            $table->dropColumn([
                'attendance_online',
                'second_service_attendance_online'
            ]);
        });
    }
};
