<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('series_id')->nullable()->constrained('series')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Free text: guest speakers are not always church members.
            $table->string('speaker');
            $table->foreignId('speaker_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->date('preached_on');
            // Seconds; the app renders this as mm:ss.
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->enum('tone', ['orange', 'purple', 'amber', 'lemon'])->default('orange');
            $table->boolean('is_live')->default(false);
            $table->string('live_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'is_published', 'preached_on']);
            $table->index(['series_id', 'preached_on']);
            $table->index('speaker');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sermons');
    }
};
