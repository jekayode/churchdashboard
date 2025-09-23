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
        // Recreate / create the service_type enum as needed
        if (Schema::hasColumn('events', 'service_type')) {
            Schema::table('events', function (Blueprint $table): void {
                $table->dropColumn('service_type');
            });
        }

        Schema::table('events', function (Blueprint $table): void {
            if (! Schema::hasColumn('events', 'service_type')) {
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
            }

            // Add fields for multiple services (like Greater Lekki's two Sunday services)
            if (! Schema::hasColumn('events', 'has_multiple_services')) {
                $table->boolean('has_multiple_services')->default(false)->after('service_name');
            }
            if (! Schema::hasColumn('events', 'second_service_time')) {
                $table->time('second_service_time')->nullable()->after('has_multiple_services');
            }
            if (! Schema::hasColumn('events', 'second_service_name')) {
                $table->string('second_service_name')->nullable()->after('second_service_time');
            }
            if (! Schema::hasColumn('events', 'second_service_end_time')) {
                $table->time('second_service_end_time')->nullable()->after('second_service_name');
            }

            // Add service end time for first service
            if (! Schema::hasColumn('events', 'service_end_time')) {
                $table->time('service_end_time')->nullable()->after('service_time');
            }

            // Add venue details
            if (! Schema::hasColumn('events', 'venue')) {
                $table->string('venue')->nullable()->after('location');
            }
            if (! Schema::hasColumn('events', 'address')) {
                $table->text('address')->nullable()->after('venue');
            }

            // Add indexes for new fields (only when we added the columns above)
            if (! Schema::hasColumn('events', 'has_multiple_services')) {
                // no index attempt when column pre-existed
            } else {
                // best effort: index only if column was just created in this run
                // Since we cannot easily detect existing index without DBAL, rely on the branch where we created the column above
            }
            if (! Schema::hasColumn('events', 'service_type')) {
                // same as above
            } else {
                // nothing; index exists conditions are unknown without DBAL
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Drop indexes if columns exist (best effort without DBAL)
            if (Schema::hasColumn('events', 'has_multiple_services')) {
                try { $table->dropIndex(['has_multiple_services']); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('events', 'service_type')) {
                try { $table->dropIndex(['branch_id', 'service_type', 'day_of_week']); } catch (\Throwable $e) {}
            }

            $dropColumns = [];
            foreach (['has_multiple_services','second_service_time','second_service_name','second_service_end_time','service_end_time','venue','address'] as $col) {
                if (Schema::hasColumn('events', $col)) {
                    $dropColumns[] = $col;
                }
            }
            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }

            if (Schema::hasColumn('events', 'service_type')) {
                $table->dropColumn('service_type');
            }
        });

        Schema::table('events', function (Blueprint $table): void {
            if (! Schema::hasColumn('events', 'service_type')) {
                $table->enum('service_type', ['sunday_service', 'midweek_service', 'special_service', 'other'])->nullable()->after('type');
            }
        });
    }
}; 