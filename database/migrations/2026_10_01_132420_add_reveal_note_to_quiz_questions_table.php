<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            // Shown on the screen and phones once answering closes.
            $table->text('reveal_note')->nullable()->after('text');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('reveal_note');
        });
    }
};
