<?php
/**
 * Laravel Database Import Script
 * Imports data exported from local database
 * Run this on your live server
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get database connection
$db = DB::connection();

// Import directory
$importDir = 'database/exports';

// Tables to import (in dependency order - important!)
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

echo "🚀 Starting database import...\n";
echo "⚠️  WARNING: This will replace existing data in these tables!\n";

// Ask for confirmation
echo "Continue? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if(trim($line) !== 'y' && trim($line) !== 'Y') {
    echo "Import cancelled.\n";
    exit;
}
fclose($handle);

$totalImported = 0;

foreach ($tables as $table) {
    $filename = "{$importDir}/{$table}.json";
    
    if (!file_exists($filename)) {
        echo "⚠️  Skipped {$table}: No export file found\n";
        continue;
    }
    
    try {
        $jsonData = file_get_contents($filename);
        $records = json_decode($jsonData, true);
        
        if (empty($records)) {
            echo "⚠️  Skipped {$table}: No records in file\n";
            continue;
        }
        
        // Clear existing data (be careful!)
        $existingCount = $db->table($table)->count();
        if ($existingCount > 0) {
            echo "🗑️  Clearing {$existingCount} existing records from {$table}...\n";
            $db->table($table)->truncate();
        }
        
        // Insert new data in chunks
        $chunks = array_chunk($records, 100);
        $inserted = 0;
        
        foreach ($chunks as $chunk) {
            $db->table($table)->insert($chunk);
            $inserted += count($chunk);
        }
        
        $totalImported += $inserted;
        echo "✅ Imported {$table}: {$inserted} records\n";
        
    } catch (Exception $e) {
        echo "❌ Error importing {$table}: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Import completed!\n";
echo "📊 Total records imported: {$totalImported}\n";
echo "🔄 You may want to run: php artisan cache:clear\n";
