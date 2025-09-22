-- PostgreSQL (Bolt)
CREATE TABLE IF NOT EXISTS property_images (
  id          VARCHAR(64) PRIMARY KEY,
  property_id VARCHAR(64) NOT NULL,
  image_url   VARCHAR(512) NOT NULL,
  image_path  VARCHAR(512) NOT NULL,
  is_main     SMALLINT NOT NULL DEFAULT 0,
  sort_order  INT DEFAULT 0,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_property_images_property ON property_images(property_id);

-- Trigger to mimic MySQL "ON UPDATE CURRENT_TIMESTAMP"
CREATE OR REPLACE FUNCTION trg_set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at := NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS set_updated_at_property_images ON property_images;
CREATE TRIGGER set_updated_at_property_images
BEFORE UPDATE ON property_images
FOR EACH ROW EXECUTE FUNCTION trg_set_updated_at();