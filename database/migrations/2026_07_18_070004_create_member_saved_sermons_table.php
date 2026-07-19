<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_saved_sermons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sermon_id')->constrained()->cascadeOnDelete();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'sermon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_saved_sermons');
    }
};
