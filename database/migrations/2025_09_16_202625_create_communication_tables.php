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
        // Communication settings per branch
        Schema::create('communication_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->enum('email_provider', ['smtp', 'resend', 'mailgun', 'ses', 'postmark'])->default('smtp');
            $table->json('email_config')->nullable();
            $table->enum('sms_provider', ['twilio', 'africas-talking'])->nullable();
            $table->json('sms_config')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('branch_id');
        });

        // Message templates
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['email', 'sms']);
            $table->string('subject')->nullable(); // for emails
            $table->text('content');
            $table->json('variables')->nullable(); // available template variables
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['branch_id', 'type']);
        });

        // Communication logs
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->enum('type', ['email', 'sms']);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('content');
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'bounced'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['branch_id', 'type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('communication_settings');
    }
};
