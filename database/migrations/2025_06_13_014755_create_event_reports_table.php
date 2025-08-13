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
        Schema::create('event_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->integer('attendance_male')->default(0);
            $table->integer('attendance_female')->default(0);
            $table->integer('attendance_children')->default(0);
            $table->integer('first_time_guests')->default(0);
            $table->integer('converts')->default(0);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('number_of_cars')->default(0);
            $table->text('notes')->nullable();
            $table->date('report_date');
            $table->timestamps();
            
            // Indexes
            $table->index('event_id');
            $table->index('reported_by');
            $table->index('report_date');
            $table->index(['event_id', 'report_date']);
            $table->unique(['event_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_reports');
    }
};
