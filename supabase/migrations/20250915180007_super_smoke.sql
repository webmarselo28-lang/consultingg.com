/*
  # Update Office Property to Rental

  Updates the office property "Офис площи / Обект "Метличина поляна 15", кв. Гоце Делчев" 
  to be a rental property with per-sqm pricing.

  1. Changes
     - Set transaction_type = 'rent' (for "Под наем")
     - Set price = 8 (displays as "8 €/кв.м")
     - Ensure featured = true for homepage display
     - Ensure active = true for visibility

  2. Target Property
     - Identified by title containing "Метличина поляна 15"
     - Should be the office property we added previously
*/

-- Update the office property to rental with per-sqm pricing
UPDATE properties 
SET 
  transaction_type = 'rent',
  price = 8.00,
  currency = 'EUR',
  featured = true,
  active = true,
  updated_at = CURRENT_TIMESTAMP
WHERE title LIKE '%Метличина поляна 15%'
  AND property_type = 'ОФИС';

-- Verify the update
SELECT 
  id,
  title,
  transaction_type,
  price,
  currency,
  property_type,
  featured,
  active,
  created_at,
  updated_at
FROM properties 
WHERE title LIKE '%Метличина поляна 15%';