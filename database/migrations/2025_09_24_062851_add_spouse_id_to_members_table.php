<?php

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
        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'spouse_id')) {
                $table->unsignedBigInteger('spouse_id')->nullable()->after('marital_status');
                $table->foreign('spouse_id')->references('id')->on('members')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'spouse_id')) {
                $table->dropForeign(['spouse_id']);
                $table->dropColumn('spouse_id');
            }
        });
    }
};
