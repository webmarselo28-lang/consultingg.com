/*
  # Update Boyana house price

  1. Changes
    - Update property price from €21,700,000 to €2,170,000
    - Property: Луксозна самостоятелна къща в кв. Бояна

  2. Security
    - No security changes needed
*/

-- Update the price for the Boyana house
UPDATE properties 
SET price = 2170000.00,
    updated_at = CURRENT_TIMESTAMP
WHERE title ILIKE '%Луксозна самостоятелна къща в кв. Бояна%'
   OR title ILIKE '%къща%Бояна%'
   OR (city_region = 'София' AND district = 'Бояна' AND property_type = 'КЪЩА');