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
        Schema::table('members', function (Blueprint $table): void {
            if (! Schema::hasColumn('members', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('members', 'surname')) {
                $table->string('surname')->nullable()->after('first_name');
            }
        });

        // Backfill first_name and surname from name if possible
        // Use database-agnostic expressions for MySQL-compatible split
        try {
            DB::statement(<<<SQL
                UPDATE members
                SET first_name = COALESCE(first_name, TRIM(SUBSTRING_INDEX(name, ' ', 1))),
                    surname    = COALESCE(surname,
                        NULLIF(TRIM(SUBSTRING(name, LENGTH(SUBSTRING_INDEX(name, ' ', 1)) + 2)), '')
                    )
                WHERE (first_name IS NULL OR first_name = '')
                   OR (surname IS NULL OR surname = '')
            SQL);
        } catch (\Throwable $e) {
            // If the DB driver does not support the above functions, do a PHP fallback
            DB::table('members')
                ->select(['id', 'name', 'first_name', 'surname'])
                ->orderBy('id')
                ->chunkById(500, function ($rows): void {
                    foreach ($rows as $row) {
                        $first = $row->first_name;
                        $last = $row->surname;
                        if ((empty($first) || empty($last)) && ! empty($row->name)) {
                            $parts = preg_split('/\s+/', trim((string) $row->name), 2);
                            $first = $first ?: ($parts[0] ?? null);
                            $last = $last ?: ($parts[1] ?? null);
                            DB::table('members')->where('id', $row->id)->update([
                                'first_name' => $first,
                                'surname' => $last,
                            ]);
                        }
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            if (Schema::hasColumn('members', 'surname')) {
                $table->dropColumn('surname');
            }
            if (Schema::hasColumn('members', 'first_name')) {
                $table->dropColumn('first_name');
            }
        });
    }
};
