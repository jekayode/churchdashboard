<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the enum by altering the column
        DB::statement("ALTER TABLE members MODIFY COLUMN marital_status ENUM('single', 'married', 'divorced', 'separated', 'widowed', 'in_a_relationship', 'engaged') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE members MODIFY COLUMN marital_status ENUM('single', 'married', 'divorced', 'widowed') NULL");
    }
};
