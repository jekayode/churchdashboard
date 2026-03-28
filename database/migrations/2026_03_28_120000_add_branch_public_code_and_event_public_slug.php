<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('public_code', 32)->nullable()->unique()->after('name');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('public_slug', 255)->nullable()->after('name');
        });

        $this->backfillBranchCodes();
        $this->backfillEventSlugs();

        Schema::table('events', function (Blueprint $table) {
            $table->unique(['branch_id', 'public_slug']);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['branch_id', 'public_slug']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('public_slug');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['public_code']);
            $table->dropColumn('public_code');
        });
    }

    private function backfillBranchCodes(): void
    {
        $branches = DB::table('branches')->orderBy('id')->get(['id', 'name']);
        $takenCodes = [];

        foreach ($branches as $branch) {
            $n = strtolower(trim((string) $branch->name));
            $code = null;

            if (str_contains($n, 'greater') && str_contains($n, 'lekki')) {
                $code = 'gl';
            } elseif (($n === 'lekki' || preg_match('/\blekki\b/', $n)) && ! str_contains($n, 'greater')) {
                $code = 'lekki';
            } elseif (str_contains($n, 'ojo')) {
                $code = 'ojo';
            } elseif (str_contains($n, 'yaba')) {
                $code = 'yaba';
            }

            if ($code !== null && isset($takenCodes[$code])) {
                $code = null;
            }

            if ($code !== null) {
                $takenCodes[$code] = true;
                DB::table('branches')->where('id', $branch->id)->update(['public_code' => $code]);
            }
        }
    }

    /**
     * Assign unique slugs per branch (name-based). Existing recurring rows each get a distinct slug.
     */
    private function backfillEventSlugs(): void
    {
        $used = [];

        $rows = DB::table('events')->orderBy('branch_id')->orderBy('id')->get(['id', 'branch_id', 'name']);

        foreach ($rows as $row) {
            $branchId = (int) $row->branch_id;
            $base = Str::slug((string) $row->name);
            if ($base === '') {
                $base = 'event';
            }

            $slug = $base;
            $i = 2;
            $key = $branchId.'|'.$slug;
            while (isset($used[$key])) {
                $slug = $base.'-'.$i;
                $i++;
                $key = $branchId.'|'.$slug;
            }
            $used[$key] = true;

            DB::table('events')->where('id', $row->id)->update(['public_slug' => $slug]);
        }
    }
};
