/*
  # Update area column to support decimal values

  1. Schema Changes
    - Change `area` column from integer to NUMERIC(10,2) to support decimal values like 1117.58
    - This allows for precise area measurements with up to 2 decimal places
    
  2. Data Migration
    - Safely convert existing integer values to decimal format
    - No data loss - all existing values remain valid
    
  3. Compatibility
    - Existing integer values (like 145, 220) continue to work
    - New decimal values (like 1117.58) are now supported
*/

-- Update the area column to support decimal values
ALTER TABLE properties 
ALTER COLUMN area TYPE NUMERIC(10,2) USING area::NUMERIC(10,2);

-- Update any existing properties that might have been affected
UPDATE properties 
SET area = ROUND(area::NUMERIC, 2) 
WHERE area IS NOT NULL;

-- Verify the change
SELECT column_name, data_type, numeric_precision, numeric_scale 
FROM information_schema.columns 
WHERE table_name = 'properties' AND column_name = 'area';