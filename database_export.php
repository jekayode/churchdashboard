<?php
/**
 * Laravel Database Export Script
 * Exports data from local database for live site import
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get database connection
$db = DB::connection();

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

$exportDir = 'database/exports';
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

echo "Starting database export...\n";

foreach ($tables as $table) {
    try {
        $records = $db->table($table)->get();
        $count = $records->count();
        
        if ($count > 0) {
            $filename = "{$exportDir}/{$table}.json";
            file_put_contents($filename, $records->toJson(JSON_PRETTY_PRINT));
            echo "✅ Exported {$table}: {$count} records\n";
        } else {
            echo "⚠️  Skipped {$table}: No records\n";
        }
    } catch (Exception $e) {
        echo "❌ Error exporting {$table}: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Export completed! Files saved in {$exportDir}/\n";
echo "Next steps:\n";
echo "1. Upload the exports folder to your live server\n";
echo "2. Run the import script on your live server\n";
