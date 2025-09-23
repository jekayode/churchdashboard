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
        Schema::table('communication_settings', function (Blueprint $table) {
            // Change sms_provider from ENUM to VARCHAR to support new providers
            $table->string('sms_provider')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            // Revert sms_provider back to ENUM (this will truncate data)
            $table->enum('sms_provider', ['twilio', 'africas-talking'])->nullable()->change();
        });
    }
};
