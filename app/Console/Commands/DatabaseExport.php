<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export {--path=database/exports : Export directory path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export database data to JSON files for migration to live server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $exportPath = $this->option('path');
        
        // Create export directory if it doesn't exist
        if (!File::exists($exportPath)) {
            File::makeDirectory($exportPath, 0755, true);
        }

        // Tables to export (in dependency order)
        $tables = [
            'roles',
            'branches', 
            'users',
            'members',
            'ministries',
            'departments', 
            'small_groups',
            'events',
            'event_registrations',
            'event_reports',
            'expenses',
            'projections',
            'user_roles',
            'member_departments',
            'member_small_groups',
            'member_status_histories',
            'small_group_meeting_reports'
        ];

        $this->info('🚀 Starting database export...');
        $totalExported = 0;

        foreach ($tables as $table) {
            try {
                $records = DB::table($table)->get();
                $count = $records->count();
                
                if ($count > 0) {
                    $filename = "{$exportPath}/{$table}.json";
                    File::put($filename, $records->toJson(JSON_PRETTY_PRINT));
                    $this->info("✅ Exported {$table}: {$count} records");
                    $totalExported += $count;
                } else {
                    $this->warn("⚠️  Skipped {$table}: No records");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error exporting {$table}: " . $e->getMessage());
            }
        }

        $this->info("\n🎉 Export completed!");
        $this->info("📊 Total records exported: {$totalExported}");
        $this->info("📁 Files saved in: {$exportPath}");
        
        $this->info("\n📋 Next steps:");
        $this->info("1. Commit and push the exports to git");
        $this->info("2. Pull the code on your live server");
        $this->info("3. Run: php artisan db:import");
        
        return 0;
    }
}
