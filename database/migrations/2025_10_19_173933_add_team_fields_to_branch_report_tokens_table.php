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
            // Add team support fields
            $table->string('team_name')->nullable()->after('name');
            $table->json('team_emails')->nullable()->after('team_name');
            $table->json('team_roles')->nullable()->after('team_emails');
            $table->boolean('is_team_token')->default(false)->after('team_roles');

            // Make email nullable for team tokens
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_report_tokens', function (Blueprint $table) {
            $table->dropColumn(['team_name', 'team_emails', 'team_roles', 'is_team_token']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
