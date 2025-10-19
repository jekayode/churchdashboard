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
        Schema::create('branch_report_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('name')->comment('Name/description of the token (e.g., "Service Chief - Main Branch")');
            $table->string('email')->nullable()->comment('Email of the service chief');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->json('allowed_events')->nullable()->comment('Specific event IDs this token can access');
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
            $table->index(['token', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_report_tokens');
    }
};
