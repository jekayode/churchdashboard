<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import {--path=database/exports : Import directory path} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import database data from JSON files (DANGER: Replaces existing data!)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $importPath = $this->option('path');
        
        if (!File::exists($importPath)) {
            $this->error("❌ Import directory not found: {$importPath}");
            return 1;
        }

        // Tables to import (in dependency order - CRITICAL!)
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

        $this->warn('🚨 WARNING: This will REPLACE ALL existing data in your database!');
        $this->warn('⚠️  Make sure you have a backup before proceeding!');
        
        if (!$this->option('force')) {
            if (!$this->confirm('Are you absolutely sure you want to continue?')) {
                $this->info('Import cancelled.');
                return 0;
            }
        }

        $this->info('🚀 Starting database import...');
        $totalImported = 0;

        foreach ($tables as $table) {
            $filename = "{$importPath}/{$table}.json";
            
            if (!File::exists($filename)) {
                $this->warn("⚠️  Skipped {$table}: No export file found");
                continue;
            }
            
            try {
                $jsonData = File::get($filename);
                $records = json_decode($jsonData, true);
                
                if (empty($records)) {
                    $this->warn("⚠️  Skipped {$table}: No records in file");
                    continue;
                }
                
                // Clear existing data
                $existingCount = DB::table($table)->count();
                if ($existingCount > 0) {
                    $this->info("🗑️  Clearing {$existingCount} existing records from {$table}...");
                    DB::table($table)->truncate();
                }
                
                // Insert new data in chunks to avoid memory issues
                $chunks = array_chunk($records, 100);
                $inserted = 0;
                
                foreach ($chunks as $chunk) {
                    DB::table($table)->insert($chunk);
                    $inserted += count($chunk);
                }
                
                $totalImported += $inserted;
                $this->info("✅ Imported {$table}: {$inserted} records");
                
            } catch (\Exception $e) {
                $this->error("❌ Error importing {$table}: " . $e->getMessage());
            }
        }

        $this->info("\n🎉 Import completed!");
        $this->info("📊 Total records imported: {$totalImported}");
        
        $this->info("\n🔄 Running cleanup commands...");
        $this->call('cache:clear');
        $this->call('config:clear');
        
        $this->info("✅ Database import successful!");
        
        return 0;
    }
}
