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
        Schema::table('branch_report_tokens', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade');
            $table->index(['event_id', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_report_tokens', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'token']);
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
