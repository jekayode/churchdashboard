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
        Schema::create('workforce_drive_signups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workforce_drive_team_id')->constrained('workforce_drive_teams')->cascadeOnDelete();
            $table->string('name');
            // One person joins one team only: email is unique across the whole drive.
            $table->string('email')->unique();
            $table->string('phone');
            $table->timestamps();

            $table->index('workforce_drive_team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workforce_drive_signups');
    }
};
