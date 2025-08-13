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
        Schema::create('projections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->year('year');
            $table->integer('attendance_target');
            $table->integer('converts_target');
            $table->integer('leaders_target');
            $table->integer('volunteers_target');
            $table->json('quarterly_breakdown')->nullable();
            $table->json('monthly_breakdown')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('branch_id');
            $table->index('created_by');
            $table->index('year');
            $table->index('deleted_at');
            $table->unique(['branch_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projections');
    }
};
