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
        Schema::table('directory_settings', function (Blueprint $table) {
            $table->boolean('business_approval_required')->default(true);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('approved_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directory_settings', function (Blueprint $table) {
            $table->dropColumn('business_approval_required');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
