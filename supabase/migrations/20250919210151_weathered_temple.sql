/*
  # Add property code column to properties table

  1. New Columns
    - `code` (text, unique) - Human-readable property codes like "prop-001"

  2. Data Migration
    - Update existing properties with sequential codes
    - Ensure all existing data gets proper codes

  3. Constraints
    - Add unique constraint on code column
    - Add index for performance

  4. Security
    - No RLS changes needed - existing policies remain
*/

-- Add the code column to properties table
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_name = 'properties' AND column_name = 'code'
  ) THEN
    ALTER TABLE properties ADD COLUMN code TEXT;
  END IF;
END $$;

-- Update existing properties with sequential codes if they don't have them
DO $$
DECLARE
  prop_record RECORD;
  counter INTEGER := 1;
  code_value TEXT;
BEGIN
  FOR prop_record IN 
    SELECT id FROM properties WHERE code IS NULL ORDER BY created_at ASC
  LOOP
    code_value := 'prop-' || LPAD(counter::TEXT, 3, '0');
    UPDATE properties SET code = code_value WHERE id = prop_record.id;
    counter := counter + 1;
  END LOOP;
END $$;

-- Add unique constraint on code column
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_name = 'properties' AND constraint_name = 'properties_code_unique'
  ) THEN
    ALTER TABLE properties ADD CONSTRAINT properties_code_unique UNIQUE (code);
  END IF;
END $$;

-- Add index on code column for performance
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_indexes
    WHERE tablename = 'properties' AND indexname = 'idx_properties_code'
  ) THEN
    CREATE INDEX idx_properties_code ON properties (code);
  END IF;
END $$;

-- Make code column NOT NULL after populating existing data
DO $$
BEGIN
  IF EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_name = 'properties' AND column_name = 'code' AND is_nullable = 'YES'
  ) THEN
    ALTER TABLE properties ALTER COLUMN code SET NOT NULL;
  END IF;
END $$;