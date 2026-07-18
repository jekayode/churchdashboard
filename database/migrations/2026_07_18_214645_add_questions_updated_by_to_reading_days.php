<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reading_days', function (Blueprint $table) {
            // Shared (network-wide) plans can be edited by any branch pastor,
            // so record who last rewrote a day's questions.
            $table->foreignId('questions_updated_by')
                ->nullable()
                ->after('questions_updated_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reading_days', function (Blueprint $table) {
            $table->dropConstrainedForeignId('questions_updated_by');
        });
    }
};
