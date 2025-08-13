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
        Schema::table('events', function (Blueprint $table): void {
            // Update service_type enum to include all the new types
            $table->dropColumn('service_type');
        });
        
        Schema::table('events', function (Blueprint $table): void {
            $table->enum('service_type', [
                'Sunday Service',
                'MidWeek', 
                'Conferences',
                'Outreach',
                'Evangelism (Beautiful Feet)',
                'Water Baptism',
                'TECi',
                'Membership Class',
                'LifeGroup Meeting',
                'other'
            ])->nullable()->after('type');
            
            // Add fields for multiple services (like Greater Lekki's two Sunday services)
            $table->boolean('has_multiple_services')->default(false)->after('service_name');
            $table->time('second_service_time')->nullable()->after('has_multiple_services');
            $table->string('second_service_name')->nullable()->after('second_service_time');
            $table->time('second_service_end_time')->nullable()->after('second_service_name');
            
            // Add service end time for first service
            $table->time('service_end_time')->nullable()->after('service_time');
            
            // Add venue details
            $table->string('venue')->nullable()->after('location');
            $table->text('address')->nullable()->after('venue');
            
            // Add indexes for new fields
            $table->index('has_multiple_services');
            $table->index(['branch_id', 'service_type', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropIndex(['branch_id', 'service_type', 'day_of_week']);
            $table->dropIndex(['has_multiple_services']);
            
            $table->dropColumn([
                'has_multiple_services',
                'second_service_time',
                'second_service_name', 
                'second_service_end_time',
                'service_end_time',
                'venue',
                'address'
            ]);
            
            $table->dropColumn('service_type');
        });
        
        Schema::table('events', function (Blueprint $table): void {
            $table->enum('service_type', ['sunday_service', 'midweek_service', 'special_service', 'other'])->nullable()->after('type');
        });
    }
}; 