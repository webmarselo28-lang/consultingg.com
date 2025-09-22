/*
  # Restore Properties Table to UUID Schema

  1. Schema Changes
    - Remove `code` column from properties table
    - Keep `id` as UUID primary key (auto-generated)
    - Remove code-related indexes

  2. Data Integrity
    - All existing properties preserved with their UUIDs
    - All property_images relationships maintained
    - No data loss during restoration

  3. API Compatibility
    - Restore UUID-based property lookups
    - Remove code-based query methods
    - Maintain all existing functionality
*/

-- Remove the code column and its constraints
DO $$
BEGIN
  -- Drop unique constraint on code if it exists
  IF EXISTS (
    SELECT 1 FROM information_schema.table_constraints 
    WHERE table_name = 'properties' AND constraint_name = 'properties_code_unique'
  ) THEN
    ALTER TABLE properties DROP CONSTRAINT properties_code_unique;
  END IF;

  -- Drop index on code if it exists
  IF EXISTS (
    SELECT 1 FROM pg_indexes 
    WHERE tablename = 'properties' AND indexname = 'idx_properties_code'
  ) THEN
    DROP INDEX idx_properties_code;
  END IF;

  -- Drop the code column if it exists
  IF EXISTS (
    SELECT 1 FROM information_schema.columns 
    WHERE table_name = 'properties' AND column_name = 'code'
  ) THEN
    ALTER TABLE properties DROP COLUMN code;
  END IF;
END $$;

-- Verify the table structure is restored
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'properties' 
ORDER BY ordinal_position;