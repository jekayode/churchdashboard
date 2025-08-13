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
        Schema::create('small_group_meeting_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('small_group_id')->constrained('small_groups')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->date('meeting_date');
            $table->time('meeting_time')->nullable();
            $table->string('meeting_location')->nullable();
            
            // Attendance counts
            $table->unsignedInteger('male_attendance')->default(0);
            $table->unsignedInteger('female_attendance')->default(0);
            $table->unsignedInteger('children_attendance')->default(0);
            $table->unsignedInteger('first_time_guests')->default(0);
            $table->unsignedInteger('converts')->default(0);
            $table->unsignedInteger('total_attendance')->default(0);
            
            // Meeting details
            $table->text('meeting_notes')->nullable();
            $table->text('prayer_requests')->nullable();
            $table->text('testimonies')->nullable();
            $table->json('attendee_names')->nullable(); // Store array of attendee names
            
            // Status and approval
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('submitted');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['small_group_id', 'meeting_date']);
            $table->index(['meeting_date']);
            $table->index(['status']);
            $table->index(['submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('small_group_meeting_reports');
    }
};
