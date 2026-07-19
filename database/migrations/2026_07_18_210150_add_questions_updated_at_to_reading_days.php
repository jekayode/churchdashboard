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
            // Set when a pastor edits the study questions. The imported plan
            // ships third-party questions the church is replacing with its own,
            // so this tracks how far through that rewrite they are.
            $table->timestamp('questions_updated_at')->nullable()->after('study_question_2');
            $table->index(['reading_plan_id', 'questions_updated_at'], 'reading_days_rewrite_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reading_days', function (Blueprint $table) {
            $table->dropIndex('reading_days_rewrite_idx');
            $table->dropColumn('questions_updated_at');
        });
    }
};
