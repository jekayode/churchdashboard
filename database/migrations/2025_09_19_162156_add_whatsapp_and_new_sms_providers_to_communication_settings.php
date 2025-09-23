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
            // Add WhatsApp provider and config
            $table->string('whatsapp_provider')->nullable()->after('sms_config');
            $table->json('whatsapp_config')->nullable()->after('whatsapp_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_provider', 'whatsapp_config']);
        });
    }
};
