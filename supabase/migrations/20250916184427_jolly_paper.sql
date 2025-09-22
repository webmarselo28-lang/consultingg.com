/*
  # Create services table for CMS

  1. New Tables
    - `services`
      - `id` (uuid, primary key)
      - `title` (varchar, required)
      - `description` (text)
      - `icon` (varchar)
      - `color` (varchar)
      - `sort_order` (integer, default 0)
      - `active` (boolean, default true)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)

  2. Security
    - Enable RLS on `services` table
    - Add policy for public read access
    - Add policy for authenticated users to manage services

  3. Sample Data
    - Insert demo services for testing
*/

-- Create services table
CREATE TABLE IF NOT EXISTS services (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  title varchar(255) NOT NULL,
  description text,
  icon varchar(255),
  color varchar(255),
  sort_order integer DEFAULT 0,
  active boolean DEFAULT true,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Enable RLS
ALTER TABLE services ENABLE ROW LEVEL SECURITY;

-- Allow public read access to active services
CREATE POLICY "Public can read active services"
  ON services
  FOR SELECT
  TO public
  USING (active = true);

-- Allow authenticated users to manage all services
CREATE POLICY "Authenticated users can manage services"
  ON services
  FOR ALL
  TO authenticated
  USING (true)
  WITH CHECK (true);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_services_active ON services(active);
CREATE INDEX IF NOT EXISTS idx_services_sort_order ON services(sort_order);

-- Insert sample data
INSERT INTO services (title, description, icon, color, sort_order, active) VALUES
('Продажба на имоти', 'Професионално съдействие при продажба на всички видове недвижими имоти', 'Home', 'from-blue-500 to-blue-600', 1, true),
('Инвестиционни консултации', 'Експертни съвети за инвестиции в недвижими имоти с висока доходност', 'TrendingUp', 'from-green-500 to-green-600', 2, true),
('Правна защита', 'Пълна правна защита и съдействие при всички сделки с имоти', 'Shield', 'from-purple-500 to-purple-600', 3, true),
('Управление на имоти', 'Професионално управление и поддръжка на вашите недвижими имоти', 'Users', 'from-orange-500 to-orange-600', 4, true),
('Оценка на имоти', 'Точна и професионална оценка на пазарната стойност на имотите', 'Award', 'from-red-500 to-red-600', 5, true),
('24/7 Поддръжка', 'Непрекъсната поддръжка и консултации за всички ваши въпроси', 'Headphones', 'from-indigo-500 to-indigo-600', 6, true)
ON CONFLICT (id) DO NOTHING;