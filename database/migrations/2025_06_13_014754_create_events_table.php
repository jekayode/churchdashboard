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
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->enum('frequency', ['once', 'weekly', 'monthly', 'quarterly', 'annually', 'recurrent'])->default('once');
            $table->enum('registration_type', ['link', 'custom_form'])->default('custom_form');
            $table->string('registration_link')->nullable();
            $table->json('custom_form_fields')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('branch_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('frequency');
            $table->index('deleted_at');
            $table->index(['branch_id', 'status']);
            $table->index(['start_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
