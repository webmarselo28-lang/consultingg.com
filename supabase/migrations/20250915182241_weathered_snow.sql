/*
  # Add Full-Text Search for Properties

  1. Extensions
    - Enable `unaccent` for accent-insensitive search
    - Enable `pg_trgm` for trigram similarity matching

  2. Search Configuration
    - Create custom text search configuration for Bulgarian
    - Add search vector column with GIN index
    - Create trigger to auto-update search vector

  3. Search Function
    - Implement search function that handles multiple terms
    - Support both exact matches and similarity search
    - Search across title, description, city_region, district, address

  4. Performance
    - GIN index for fast full-text search
    - Automatic search vector updates via trigger
    - Optimized for Bulgarian text with unaccent support
*/

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS unaccent;
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- Create custom text search configuration for Bulgarian with unaccent
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_ts_config WHERE cfgname = 'bulgarian_unaccent'
  ) THEN
    CREATE TEXT SEARCH CONFIGURATION bulgarian_unaccent (COPY = simple);
    ALTER TEXT SEARCH CONFIGURATION bulgarian_unaccent
      ALTER MAPPING FOR asciiword, asciihword, hword_asciipart, word, hword, hword_part
      WITH unaccent, simple;
  END IF;
END $$;

-- Add search vector column if it doesn't exist
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns 
    WHERE table_name = 'properties' AND column_name = 'search_vector'
  ) THEN
    ALTER TABLE properties ADD COLUMN search_vector tsvector;
  END IF;
END $$;

-- Create function to build search vector
CREATE OR REPLACE FUNCTION build_property_search_vector(
  title text,
  description text DEFAULT '',
  city_region text DEFAULT '',
  district text DEFAULT '',
  address text DEFAULT ''
) RETURNS tsvector AS $$
BEGIN
  RETURN to_tsvector('bulgarian_unaccent', 
    COALESCE(title, '') || ' ' ||
    COALESCE(description, '') || ' ' ||
    COALESCE(city_region, '') || ' ' ||
    COALESCE(district, '') || ' ' ||
    COALESCE(address, '')
  );
END;
$$ LANGUAGE plpgsql IMMUTABLE;

-- Update existing search vectors
UPDATE properties 
SET search_vector = build_property_search_vector(
  title, 
  description, 
  city_region, 
  district, 
  address
);

-- Create trigger function to auto-update search vector
CREATE OR REPLACE FUNCTION update_property_search_vector()
RETURNS trigger AS $$
BEGIN
  NEW.search_vector = build_property_search_vector(
    NEW.title,
    NEW.description,
    NEW.city_region,
    NEW.district,
    NEW.address
  );
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger if it doesn't exist
DROP TRIGGER IF EXISTS update_property_search_vector_trigger ON properties;
CREATE TRIGGER update_property_search_vector_trigger
  BEFORE INSERT OR UPDATE ON properties
  FOR EACH ROW EXECUTE FUNCTION update_property_search_vector();

-- Create GIN index for fast full-text search
CREATE INDEX IF NOT EXISTS idx_properties_search_vector 
ON properties USING GIN(search_vector);

-- Create additional trigram index for similarity search
CREATE INDEX IF NOT EXISTS idx_properties_title_trgm 
ON properties USING GIN(title gin_trgm_ops);

CREATE INDEX IF NOT EXISTS idx_properties_description_trgm 
ON properties USING GIN(description gin_trgm_ops);

-- Create search function for properties
CREATE OR REPLACE FUNCTION search_properties(
  search_query text,
  limit_count integer DEFAULT 50,
  offset_count integer DEFAULT 0
) RETURNS TABLE(
  id uuid,
  title varchar,
  description text,
  price numeric,
  currency varchar,
  transaction_type varchar,
  property_type varchar,
  city_region varchar,
  district varchar,
  address text,
  area numeric,
  bedrooms integer,
  bathrooms integer,
  floors integer,
  floor_number integer,
  terraces integer,
  construction_type varchar,
  condition_type varchar,
  heating varchar,
  exposure varchar,
  year_built integer,
  furnishing_level varchar,
  has_elevator boolean,
  has_garage boolean,
  has_southern_exposure boolean,
  new_construction boolean,
  featured boolean,
  active boolean,
  created_at timestamp,
  updated_at timestamp,
  search_rank real
) AS $$
BEGIN
  RETURN QUERY
  SELECT 
    p.id,
    p.title,
    p.description,
    p.price,
    p.currency,
    p.transaction_type,
    p.property_type,
    p.city_region,
    p.district,
    p.address,
    p.area,
    p.bedrooms,
    p.bathrooms,
    p.floors,
    p.floor_number,
    p.terraces,
    p.construction_type,
    p.condition_type,
    p.heating,
    p.exposure,
    p.year_built,
    p.furnishing_level,
    p.has_elevator,
    p.has_garage,
    p.has_southern_exposure,
    p.new_construction,
    p.featured,
    p.active,
    p.created_at,
    p.updated_at,
    (
      ts_rank(p.search_vector, plainto_tsquery('bulgarian_unaccent', search_query)) +
      CASE 
        WHEN p.title ILIKE '%' || search_query || '%' THEN 0.5
        ELSE 0
      END +
      CASE 
        WHEN p.featured THEN 0.2
        ELSE 0
      END
    ) as search_rank
  FROM properties p
  WHERE 
    p.active = true
    AND (
      p.search_vector @@ plainto_tsquery('bulgarian_unaccent', search_query)
      OR p.title ILIKE '%' || search_query || '%'
      OR p.description ILIKE '%' || search_query || '%'
      OR p.city_region ILIKE '%' || search_query || '%'
      OR p.district ILIKE '%' || search_query || '%'
      OR p.address ILIKE '%' || search_query || '%'
    )
  ORDER BY search_rank DESC, p.featured DESC, p.created_at DESC
  LIMIT limit_count OFFSET offset_count;
END;
$$ LANGUAGE plpgsql;