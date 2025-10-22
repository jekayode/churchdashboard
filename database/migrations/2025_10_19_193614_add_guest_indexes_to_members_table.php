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
        Schema::table('members', function (Blueprint $table) {
            // Add indexes to optimize guest queries
            try {
                $table->index(['member_status', 'registration_source']);
            } catch (\Throwable $e) {
                // Index might already exist
            }

            try {
                $table->index('created_at');
            } catch (\Throwable $e) {
                // Index might already exist
            }

            try {
                $table->index('staying_intention');
            } catch (\Throwable $e) {
                // Index might already exist
            }

            // Note: ['branch_id', 'member_status'] index already exists in create_members_table migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['member_status', 'registration_source']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['staying_intention']);
            // Note: ['branch_id', 'member_status'] index is from create_members_table migration
        });
    }
};
