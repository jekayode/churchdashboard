<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sermons', function (Blueprint $table) {
            // Most sermons are published to YouTube rather than uploaded as
            // audio, so a link is the common case, not the exception.
            $table->string('video_url')->nullable()->after('live_url');
        });
    }

    public function down(): void
    {
        Schema::table('sermons', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
