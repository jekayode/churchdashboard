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
        Schema::create('guest_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('follow_up_type', ['phone_call', 'whatsapp', 'sms', 'email', 'in_person']);
            $table->timestamp('contact_date')->nullable();
            $table->enum('contact_status', ['pending', 'contacted', 'no_answer', 'follow_up_needed', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('next_follow_up_date')->nullable();
            $table->enum('outcome', ['interested_in_membership', 'needs_prayer', 'attending_small_group', 'other'])->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['member_id', 'contact_status']);
            $table->index(['assigned_to', 'contact_status']);
            $table->index('next_follow_up_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_follow_ups');
    }
};
