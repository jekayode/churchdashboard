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
            // Service-specific fields
            $table->enum('service_type', ['sunday_service', 'midweek_service', 'special_service', 'other'])->nullable()->after('type');
            $table->integer('day_of_week')->nullable()->after('service_type'); // 0=Sunday, 1=Monday, etc.
            $table->time('service_time')->nullable()->after('day_of_week');
            $table->string('service_name')->nullable()->after('service_time'); // e.g., "First Service", "Second Service"
            
            // Recurring event fields
            $table->foreignId('parent_event_id')->nullable()->constrained('events')->onDelete('cascade')->after('service_name');
            $table->boolean('is_recurring')->default(false)->after('parent_event_id');
            $table->boolean('is_recurring_instance')->default(false)->after('is_recurring');
            $table->json('recurrence_rules')->nullable()->after('is_recurring_instance'); // Store complex recurrence rules
            $table->date('recurrence_end_date')->nullable()->after('recurrence_rules');
            $table->integer('max_occurrences')->nullable()->after('recurrence_end_date');
            
            // Indexes for performance
            $table->index('service_type');
            $table->index('day_of_week');
            $table->index('parent_event_id');
            $table->index(['branch_id', 'service_type']);
            $table->index(['branch_id', 'day_of_week', 'service_time']);
            $table->index(['is_recurring', 'is_recurring_instance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropForeign(['parent_event_id']);
            $table->dropIndex(['branch_id', 'service_type']);
            $table->dropIndex(['branch_id', 'day_of_week', 'service_time']);
            $table->dropIndex(['is_recurring', 'is_recurring_instance']);
            $table->dropIndex(['service_type']);
            $table->dropIndex(['day_of_week']);
            $table->dropIndex(['parent_event_id']);
            
            $table->dropColumn([
                'service_type',
                'day_of_week',
                'service_time',
                'service_name',
                'parent_event_id',
                'is_recurring',
                'is_recurring_instance',
                'recurrence_rules',
                'recurrence_end_date',
                'max_occurrences'
            ]);
        });
    }
};
