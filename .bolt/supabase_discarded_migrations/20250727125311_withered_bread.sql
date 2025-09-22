-- PostgreSQL Admin User Seed Script
-- How to execute:
-- 1. Connect to your PostgreSQL database
-- 2. Run: \i /path/to/seed_admin_pg.sql
-- 3. Or copy-paste the INSERT statement in pgAdmin/psql

-- Ensure required extensions are available
-- (These should already be created in main schema)
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
-- CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Insert admin user
INSERT INTO users(id, email, password_hash, name, role)
VALUES (
    uuid_generate_v4(),
    'georgiev@consultingg.com',
    crypt('PoloSport88*', gen_salt('bf')),
    'Georgi Georgiev',
    'admin'
);

-- Verify the user was created
SELECT id, email, name, role, created_at 
FROM users 
WHERE email = 'georgiev@consultingg.com';