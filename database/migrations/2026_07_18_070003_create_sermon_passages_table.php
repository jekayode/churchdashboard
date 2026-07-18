<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sermon_passages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained()->cascadeOnDelete();
            // Human reference ("Psalm 1:1-3") plus parts for a Bible-text lookup.
            $table->string('reference');
            $table->string('book')->nullable();
            $table->unsignedSmallInteger('chapter')->nullable();
            $table->string('verses')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['sermon_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sermon_passages');
    }
};
