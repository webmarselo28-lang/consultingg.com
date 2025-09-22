/*
  # Add pricing mode support for properties

  1. Schema Changes
    - Add `pricing_mode` column to properties table
    - Support values: 'total', 'per_month', 'per_sqm'
    - Default to 'total' for existing properties

  2. Data Migration
    - Set existing sale properties to 'total' mode
    - Set existing rent properties to 'per_month' mode
    - Update Goce Delchev office to per_sqm pricing

  3. Constraints
    - Add check constraint for valid pricing modes
*/

-- Add pricing_mode column if it doesn't exist
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns 
    WHERE table_name = 'properties' AND column_name = 'pricing_mode'
  ) THEN
    ALTER TABLE properties ADD COLUMN pricing_mode TEXT DEFAULT 'total';
  END IF;
END $$;

-- Set default pricing modes for existing data
UPDATE properties 
SET pricing_mode = CASE 
  WHEN transaction_type = 'sale' THEN 'total'
  WHEN transaction_type = 'rent' THEN 'per_month'
  ELSE 'total'
END
WHERE pricing_mode IS NULL OR pricing_mode = '';

-- Update Goce Delchev office property to per-sqm pricing
UPDATE properties 
SET 
  transaction_type = 'rent',
  price = 8,
  currency = 'EUR',
  pricing_mode = 'per_sqm',
  active = true,
  featured = true
WHERE title ILIKE '%Метличина поляна 15%' 
  AND title ILIKE '%Гоце Делчев%';

-- Add constraint for pricing_mode values (only if it doesn't exist)
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints 
    WHERE constraint_name = 'pricing_mode_check' 
    AND table_name = 'properties'
  ) THEN
    ALTER TABLE properties 
    ADD CONSTRAINT pricing_mode_check 
    CHECK (pricing_mode IN ('total', 'per_month', 'per_sqm'));
  END IF;
END $$;

-- Create index for pricing_mode if it doesn't exist
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_indexes 
    WHERE indexname = 'idx_properties_pricing_mode'
  ) THEN
    CREATE INDEX idx_properties_pricing_mode ON properties(pricing_mode);
  END IF;
END $$;