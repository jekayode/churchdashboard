<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();

            // Polymorphic and nullable: a note may hang off a sermon or a
            // reading day, or stand alone as a personal note.
            $table->nullableMorphs('notable');

            $table->string('title')->nullable();
            $table->longText('body');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['member_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
