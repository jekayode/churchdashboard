<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL/MariaDB
        if (config('database.default') === 'mysql') {
            // Step 1: Temporarily set invalid values to NULL
            DB::statement("UPDATE members SET marital_status = NULL WHERE marital_status NOT IN ('single', 'married', 'divorced', 'widowed')");

            // Step 2: Modify the enum to include new values
            DB::statement("ALTER TABLE members MODIFY COLUMN marital_status ENUM('single', 'in-relationship', 'engaged', 'married', 'separated', 'divorced', 'widowed') NULL");
        }
        // For SQLite, we skip this as enums don't exist in SQLite
        // For PostgreSQL, modify column type
        elseif (config('database.default') === 'pgsql') {
            Schema::table('members', function (Blueprint $table): void {
                $table->string('marital_status')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            // Temporarily set new values to NULL
            DB::statement("UPDATE members SET marital_status = NULL WHERE marital_status IN ('in-relationship', 'engaged', 'separated')");

            // Revert enum to original values
            DB::statement("ALTER TABLE members MODIFY COLUMN marital_status ENUM('single', 'married', 'divorced', 'widowed') NULL");
        } elseif (config('database.default') === 'pgsql') {
            Schema::table('members', function (Blueprint $table): void {
                $table->string('marital_status')->nullable()->change();
            });
        }
    }
};
