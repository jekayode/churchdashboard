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
        Schema::create('member_small_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('small_group_id')->constrained('small_groups')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index('member_id');
            $table->index('small_group_id');
            $table->index('joined_at');
            $table->unique(['member_id', 'small_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_small_groups');
    }
};
