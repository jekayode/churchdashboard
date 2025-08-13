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
        Schema::create('member_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->enum('previous_status', ['visitor', 'member', 'volunteer', 'leader', 'minister'])->nullable();
            $table->enum('new_status', ['visitor', 'member', 'volunteer', 'leader', 'minister']);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['member_id', 'changed_at']);
            $table->index('changed_by');
            $table->index('new_status');
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_status_histories');
    }
};
