-- Remove the old migration record
DELETE FROM migrations WHERE migration = '2025_01_02_000001_add_new_service_types_to_events';

-- If the events table exists but the migration failed, we might need to manually check table structure
-- Run this to see what columns exist in events table:
-- DESCRIBE events;
