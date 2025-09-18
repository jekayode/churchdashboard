<?php

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
        Schema::table('members', function (Blueprint $table) {
            // Guest form fields
            $table->enum('preferred_call_time', ['anytime', 'morning', 'afternoon', 'evening'])->nullable();
            $table->text('home_address')->nullable();
            $table->enum('age_group', ['15-20', '21-25', '26-30', '31-35', '36-40', 'above-40'])->nullable();
            $table->text('prayer_request')->nullable();
            $table->enum('discovery_source', ['social-media', 'word-of-mouth', 'billboard', 'email', 'website', 'promotional-material', 'radio-tv', 'outreach'])->nullable();
            $table->enum('staying_intention', ['yes-for-sure', 'visit-when-in-town', 'just-visiting', 'weighing-options'])->nullable();
            $table->string('closest_location')->nullable();
            $table->text('additional_info')->nullable();
            $table->timestamp('consent_given_at')->nullable();
            $table->string('consent_ip', 45)->nullable();
            $table->tinyInteger('profile_completion_percentage')->default(0);
            $table->enum('registration_source', ['guest-form', 'admin-created', 'imported'])->default('admin-created');
        });
        
        // Update existing enums
        DB::statement("ALTER TABLE members MODIFY COLUMN gender ENUM('male', 'female', 'prefer-not-to-say') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_call_time',
                'home_address', 
                'age_group',
                'prayer_request',
                'discovery_source',
                'staying_intention',
                'closest_location',
                'additional_info',
                'consent_given_at',
                'consent_ip',
                'profile_completion_percentage',
                'registration_source'
            ]);
        });
        
        // Revert gender enum
        DB::statement("ALTER TABLE members MODIFY COLUMN gender ENUM('male', 'female') NULL");
    }
};
