/*
  # Add sections table for dynamic content management

  1. New Tables
    - `sections`
      - `id` (uuid, primary key)
      - `title` (text)
      - `content` (text)
      - `section_type` (text) - hero, about, services, testimonials, contact, footer
      - `sort_order` (integer)
      - `active` (boolean)
      - `meta_data` (jsonb) - for additional configuration
      - `created_at` (timestamp)
      - `updated_at` (timestamp)

  2. Security
    - Enable RLS on `sections` table
    - Add policy for public read access
    - Add policy for authenticated admin write access

  3. Sample Data
    - Insert default sections for hero, about, services
*/

-- Create sections table
CREATE TABLE IF NOT EXISTS sections (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  title text NOT NULL,
  content text NOT NULL,
  section_type text NOT NULL CHECK (section_type IN ('hero', 'about', 'services', 'testimonials', 'contact', 'footer')),
  sort_order integer DEFAULT 0,
  active boolean DEFAULT true,
  meta_data jsonb,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Enable RLS
ALTER TABLE sections ENABLE ROW LEVEL SECURITY;

-- Create policies
CREATE POLICY "Sections are viewable by everyone"
  ON sections
  FOR SELECT
  USING (active = true);

CREATE POLICY "Sections are manageable by authenticated users"
  ON sections
  FOR ALL
  TO authenticated
  USING (true)
  WITH CHECK (true);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_sections_type ON sections(section_type);
CREATE INDEX IF NOT EXISTS idx_sections_sort_order ON sections(sort_order);
CREATE INDEX IF NOT EXISTS idx_sections_active ON sections(active);

-- Create updated_at trigger
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_sections_updated_at
    BEFORE UPDATE ON sections
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Insert sample sections
INSERT INTO sections (title, content, section_type, sort_order, active, meta_data) VALUES
(
  'Добре дошли в ConsultingG Real Estate',
  '<h1>Намерете своя перфектен дом</h1><p>Водещата платформа за недвижими имоти в България с над 15 години опит.</p>',
  'hero',
  1,
  true,
  '{"background_image": "/images/hero-bg.jpg", "cta_text": "Започнете търсенето", "cta_link": "/properties"}'
),
(
  'За нас',
  '<h2>ConsultingG Real Estate</h2><p>Водещата компания за недвижими имоти в България с над 15 години опит в сферата. Нашият екип от професионалисти предлага пълен спектър от услуги - от търсене и оценка на имоти до правно съдействие при сделки.</p><h3>Нашата мисия</h3><p>Да предоставяме най-качествените услуги в областта на недвижимите имоти, като помагаме на клиентите ни да намерят перфектния дом или да направят печеливша инвестиция.</p>',
  'about',
  2,
  true,
  '{"show_stats": true, "stats": {"experience": "15+", "clients": "1000+", "properties": "500+"}}'
),
(
  'Нашите услуги',
  '<h2>Пълен спектър от услуги</h2><p>Предлагаме професионални услуги в областта на недвижимите имоти.</p>',
  'services',
  3,
  true,
  '{"layout": "grid", "columns": 3, "show_icons": true}'
),
(
  'Контакти',
  '<h2>Свържете се с нас</h2><p>Нашият екип е на ваше разположение за всички въпроси.</p>',
  'contact',
  4,
  true,
  '{"show_map": true, "office_hours": "Пон-Пет: 09:00-18:00", "phone": "0888825445", "email": "office@consultingg.com"}'
);