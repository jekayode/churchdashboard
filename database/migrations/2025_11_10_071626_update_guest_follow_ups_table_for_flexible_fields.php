<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guest_follow_ups', function (Blueprint $table) {
            // Change follow_up_type from enum to string(100) to allow custom values
            DB::statement("ALTER TABLE `guest_follow_ups` MODIFY COLUMN `follow_up_type` VARCHAR(100) NOT NULL");
            
            // Change contact_status enum to include all values from form validation
            // First, we need to update any existing data that doesn't match new values
            DB::statement("UPDATE `guest_follow_ups` SET `contact_status` = 'pending' WHERE `contact_status` = 'contacted'");
            DB::statement("UPDATE `guest_follow_ups` SET `contact_status` = 'pending' WHERE `contact_status` = 'no_answer'");
            DB::statement("UPDATE `guest_follow_ups` SET `contact_status` = 'pending' WHERE `contact_status` = 'follow_up_needed'");
            
            // Now change the enum to match form validation
            DB::statement("ALTER TABLE `guest_follow_ups` MODIFY COLUMN `contact_status` ENUM('pending', 'completed', 'rescheduled', 'cancelled') NOT NULL DEFAULT 'pending'");
            
            // Change outcome from enum to string(500) to allow custom values
            DB::statement("ALTER TABLE `guest_follow_ups` MODIFY COLUMN `outcome` VARCHAR(500) NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_follow_ups', function (Blueprint $table) {
            // Revert follow_up_type back to enum
            DB::statement("ALTER TABLE `guest_follow_ups` MODIFY COLUMN `follow_up_type` ENUM('phone_call', 'whatsapp', 'sms', 'email', 'in_person') NOT NULL");
            
            // Revert contact_status back to original enum
            DB::statement("ALTER TABLE `guest_follow_ups` MODIFY COLUMN `contact_status` ENUM('pending', 'contacted', 'no_answer', 'follow_up_needed', 'completed') NOT NULL DEFAULT 'pending'");
            
            // Revert outcome back to enum
            DB::statement("ALTER TABLE `guest_follow_ups` MODIFY COLUMN `outcome` ENUM('interested_in_membership', 'needs_prayer', 'attending_small_group', 'other') NULL");
        });
    }
};
