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
        Schema::table('event_reports', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique('event_reports_event_id_report_date_unique');

            // Add a new unique constraint that includes reported_by
            // This allows multiple reports for the same event/date but prevents duplicate submissions from the same person
            $table->unique(['event_id', 'report_date', 'reported_by'], 'event_reports_event_date_reporter_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_reports', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('event_reports_event_date_reporter_unique');

            // Restore the original unique constraint
            $table->unique(['event_id', 'report_date'], 'event_reports_event_id_report_date_unique');
        });
    }
};
