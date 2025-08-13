<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the import functionality
    | including timeouts, memory limits, and batch sizes.
    |
    */

    'timeout' => env('IMPORT_TIMEOUT', 300), // 5 minutes default
    'memory_limit' => env('IMPORT_MEMORY_LIMIT', '512M'),
    'max_file_size' => env('IMPORT_MAX_FILE_SIZE', 10 * 1024 * 1024), // 10MB default
    
    'batch_sizes' => [
        'members' => env('IMPORT_MEMBERS_BATCH_SIZE', 50),
        'event_reports' => env('IMPORT_EVENT_REPORTS_BATCH_SIZE', 50),
        'ministries' => env('IMPORT_MINISTRIES_BATCH_SIZE', 100),
        'departments' => env('IMPORT_DEPARTMENTS_BATCH_SIZE', 100),
        'small_groups' => env('IMPORT_SMALL_GROUPS_BATCH_SIZE', 100),
    ],
    
    'chunk_sizes' => [
        'members' => env('IMPORT_MEMBERS_CHUNK_SIZE', 100),
        'event_reports' => env('IMPORT_EVENT_REPORTS_CHUNK_SIZE', 100),
        'ministries' => env('IMPORT_MINISTRIES_CHUNK_SIZE', 200),
        'departments' => env('IMPORT_DEPARTMENTS_CHUNK_SIZE', 200),
        'small_groups' => env('IMPORT_SMALL_GROUPS_CHUNK_SIZE', 200),
    ],
    
    'allowed_extensions' => ['xlsx', 'xls', 'csv'],
]; 