/*
  # Add thumbnail support to property images

  1. Schema Changes
    - Add `thumbnail_url` column to `property_images` table
    - Add `file_size` column for better file management
    - Add `mime_type` column for file type tracking

  2. Indexes
    - Add index on `thumbnail_url` for faster queries

  3. Security
    - Maintain existing RLS policies
*/

-- Add thumbnail support columns
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_name = 'property_images' AND column_name = 'thumbnail_url'
  ) THEN
    ALTER TABLE property_images ADD COLUMN thumbnail_url character varying(500);
  END IF;
END $$;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_name = 'property_images' AND column_name = 'file_size'
  ) THEN
    ALTER TABLE property_images ADD COLUMN file_size integer DEFAULT 0;
  END IF;
END $$;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_name = 'property_images' AND column_name = 'mime_type'
  ) THEN
    ALTER TABLE property_images ADD COLUMN mime_type character varying(100);
  END IF;
END $$;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_property_images_thumbnail_url ON property_images(thumbnail_url);
CREATE INDEX IF NOT EXISTS idx_property_images_mime_type ON property_images(mime_type);