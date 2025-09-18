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
        // Email campaigns
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->enum('trigger_event', ['guest-registration', 'member-created', 'custom']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['branch_id', 'is_active']);
        });

        // Email campaign steps
        Schema::create('email_campaign_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->integer('step_order');
            $table->integer('delay_days')->default(0);
            $table->foreignId('template_id')->constrained('message_templates')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['campaign_id', 'step_order']);
        });

        // Email campaign enrollments
        Schema::create('email_campaign_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->integer('current_step')->default(0);
            $table->timestamp('next_send_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'campaign_id']);
            $table->index(['campaign_id', 'next_send_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaign_enrollments');
        Schema::dropIfExists('email_campaign_steps');
        Schema::dropIfExists('email_campaigns');
    }
};
