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
        Schema::create('guest_membership_pipeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->enum('interest_level', ['high', 'medium', 'low']);
            $table->enum('pipeline_stage', ['new_interest', 'contacted', 'info_sent', 'class_scheduled', 'class_attended', 'converted', 'not_interested'])->default('new_interest');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('info_sent_at')->nullable();
            $table->timestamp('class_scheduled_at')->nullable();
            $table->timestamp('class_attended_at')->nullable();
            $table->timestamp('conversion_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['member_id', 'pipeline_stage']);
            $table->index(['assigned_to', 'pipeline_stage']);
            $table->index(['interest_level', 'pipeline_stage']);
            $table->index('conversion_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_membership_pipeline');
    }
};
