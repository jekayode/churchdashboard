<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_plan_enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_plan_id')->constrained()->cascadeOnDelete();
            $table->date('started_on');
            $table->boolean('is_active')->default(true);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'reading_plan_id']);
            $table->index(['member_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_plan_enrolments');
    }
};
