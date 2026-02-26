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
        Schema::table('members', function (Blueprint $table): void {
            $table->unsignedTinyInteger('birthday_month')->nullable()->after('date_of_birth');
            $table->unsignedTinyInteger('birthday_day')->nullable()->after('birthday_month');

            // Add indexes for efficient birthday queries
            $table->index(['birthday_month', 'birthday_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropIndex(['birthday_month', 'birthday_day']);
            $table->dropColumn(['birthday_month', 'birthday_day']);
        });
    }
};
