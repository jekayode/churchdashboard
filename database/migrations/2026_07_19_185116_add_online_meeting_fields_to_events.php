<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Where an online event actually happens. Distinct from
            // registration_link, which is where people sign up beforehand.
            $table->boolean('is_online')->default(false)->after('location');
            $table->string('online_url')->nullable()->after('is_online');
            $table->string('online_platform')->nullable()->after('online_url');
            // Zoom and similar often need a passcode alongside the link.
            $table->string('online_passcode')->nullable()->after('online_platform');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_online', 'online_url', 'online_platform', 'online_passcode']);
        });
    }
};
