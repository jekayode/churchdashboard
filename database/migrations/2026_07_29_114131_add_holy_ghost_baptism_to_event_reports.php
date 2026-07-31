<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_reports', function (Blueprint $table) {
            // The one attendance figure the Sunday service sheet records that the
            // report did not. Nullable, because it is often zero and should never
            // block a submission. Second-service twin, since reports are per two
            // services like everything else here.
            $table->unsignedInteger('holy_ghost_baptism')->nullable()->after('converts');
            $table->unsignedInteger('second_service_holy_ghost_baptism')->nullable()->after('second_service_converts');
        });
    }

    public function down(): void
    {
        Schema::table('event_reports', function (Blueprint $table) {
            $table->dropColumn(['holy_ghost_baptism', 'second_service_holy_ghost_baptism']);
        });
    }
};
