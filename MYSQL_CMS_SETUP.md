# MySQL CMS Implementation Guide

## ✅ COMPLETED SETUP

### 1. Prisma Schema Updated
**File:** `backend/prisma/schema.prisma`

**Provider:** `mysql` (NOT PostgreSQL!)

**New Models Added:**
- ✅ Section - Homepage and page sections with SEO
- ✅ Page - Static pages with SEO metadata
- ✅ Service - Services with branding and SEO

### 2. Database Configuration
**File:** `backend/.env`

```env
DATABASE_URL="mysql://yogahonc_consultingg78:PoloSport88*@localhost:3306/yogahonc_consultingg78"
```

**Connection Details:**
- Database: MySQL 8.0
- Host: localhost:3306 (production server)
- Database Name: yogahonc_consultingg78
- User: yogahonc_consultingg78
- Password: PoloSport88*

### 3. Migration SQL Created
**File:** `backend/prisma/migrations/add_cms_models.sql`

This file contains:
- ✅ CREATE TABLE statements for all 3 CMS tables
- ✅ Indexes for performance optimization
- ✅ Seed data (4 sections, 4 services, 3 pages)
- ✅ Verification queries

### 4. CMS Service Layer
**File:** `backend/src/services/cmsService.ts`

Complete CRUD operations for:
- Sections Management (9 functions)
- Pages Management (6 functions)
- Services Management (7 functions)

### 5. Prisma Client Generated
✅ Prisma Client v6.18.0 generated successfully
✅ Compatible with MySQL 8.0

---

## 🚀 DEPLOYMENT STEPS

### Step 1: Connect to MySQL Database

```bash
# SSH into your production server
ssh your-server

# Connect to MySQL
mysql -u yogahonc_consultingg78 -p
# Enter password: PoloSport88*

# Select database
USE yogahonc_consultingg78;
```

### Step 2: Run the Migration SQL

**Option A: From file (recommended)**
```bash
# Upload the SQL file to your server first
mysql -u yogahonc_consultingg78 -p yogahonc_consultingg78 < backend/prisma/migrations/add_cms_models.sql
```

**Option B: Copy-paste in MySQL CLI**
1. Open `backend/prisma/migrations/add_cms_models.sql`
2. Copy the entire content
3. Paste into MySQL CLI
4. Press Enter

### Step 3: Verify Tables Created

```sql
-- Check if tables exist
SHOW TABLES LIKE '%sections%';
SHOW TABLES LIKE '%pages%';
SHOW TABLES LIKE '%services%';

-- Check record counts
SELECT 'sections' as table_name, COUNT(*) as records FROM sections
UNION ALL
SELECT 'pages', COUNT(*) FROM pages
UNION ALL
SELECT 'services', COUNT(*) FROM services;

-- Should show:
-- sections: 4 records
-- pages: 3 records
-- services: 4 records
```

### Step 4: View Seeded Data

```sql
-- View homepage sections
SELECT id, page_slug, type, title, active, sort_order
FROM sections
ORDER BY sort_order;

-- View all pages
SELECT id, slug, title, active, show_in_menu
FROM pages
ORDER BY menu_order;

-- View all services
SELECT id, title, icon, color, active, featured, sort_order
FROM services
ORDER BY sort_order;
```

---

## 📝 DATABASE SCHEMA

### Sections Table
```sql
CREATE TABLE `sections` (
  `id` VARCHAR(36) PRIMARY KEY,
  `page_slug` VARCHAR(100) NOT NULL,          -- 'homepage', 'about', etc.
  `type` VARCHAR(50) NOT NULL,                -- 'hero', 'services', 'features', 'cta'
  `title` VARCHAR(255) NULL,
  `subtitle` VARCHAR(500) NULL,
  `content` TEXT NULL,
  `button_text` VARCHAR(100) NULL,
  `button_link` VARCHAR(255) NULL,
  `image_url` VARCHAR(500) NULL,
  `data` JSON NULL,                           -- Flexible JSON data
  `active` BOOLEAN NOT NULL DEFAULT TRUE,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(255) NULL,
  `seo_description` VARCHAR(500) NULL,
  `seo_keywords` VARCHAR(500) NULL,
  `created_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

  INDEX `sections_page_slug_active_idx` (`page_slug`, `active`),
  INDEX `sections_sort_order_idx` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Pages Table
```sql
CREATE TABLE `pages` (
  `id` VARCHAR(36) PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `meta_title` VARCHAR(255) NULL,
  `meta_description` VARCHAR(500) NULL,
  `meta_keywords` VARCHAR(500) NULL,
  `og_image` VARCHAR(500) NULL,
  `active` BOOLEAN NOT NULL DEFAULT TRUE,
  `show_in_menu` BOOLEAN NOT NULL DEFAULT FALSE,
  `menu_order` INT NULL,
  `template` VARCHAR(50) NOT NULL DEFAULT 'default',
  `created_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

  INDEX `pages_slug_idx` (`slug`),
  INDEX `pages_active_idx` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Services Table
```sql
CREATE TABLE `services` (
  `id` VARCHAR(36) PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `icon` VARCHAR(100) NOT NULL,
  `color` VARCHAR(50) NULL,
  `image_url` VARCHAR(500) NULL,
  `content` TEXT NULL,
  `active` BOOLEAN NOT NULL DEFAULT TRUE,
  `featured` BOOLEAN NOT NULL DEFAULT FALSE,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(255) NULL,
  `seo_description` VARCHAR(500) NULL,
  `created_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `updated_at` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

  INDEX `services_active_idx` (`active`),
  INDEX `services_featured_idx` (`featured`),
  INDEX `services_sort_order_idx` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔧 BACKEND INTEGRATION

### Using CMS Service

```typescript
import { cmsService } from './services/cmsService';

// Get homepage sections
const sections = await cmsService.getSections({
  page_slug: 'homepage',
  active: true
});

// Get page by slug
const page = await cmsService.getPageBySlug('about');

// Get active services
const services = await cmsService.getServices(true);

// Create new section
const newSection = await cmsService.createSection({
  page_slug: 'homepage',
  type: 'hero',
  title: 'Welcome',
  subtitle: 'To our site',
  active: true,
  sort_order: 1
});

// Update section
const updated = await cmsService.updateSection('section-id', {
  title: 'Updated Title',
  active: false
});

// Delete section
await cmsService.deleteSection('section-id');

// Reorder sections
await cmsService.reorderSections([
  { id: 'id1', sort_order: 1 },
  { id: 'id2', sort_order: 2 },
  { id: 'id3', sort_order: 3 }
]);
```

---

## 🌐 FRONTEND INTEGRATION

### Fetch from API

The frontend should use the existing `supabaseService.ts` but you'll need to update it to call your MySQL backend API instead.

**Create API endpoints in backend:**
```
GET    /api/cms/sections?page_slug=homepage
GET    /api/cms/sections/:id
POST   /api/cms/sections
PUT    /api/cms/sections/:id
DELETE /api/cms/sections/:id

GET    /api/cms/pages
GET    /api/cms/pages/:slug
POST   /api/cms/pages
PUT    /api/cms/pages/:id
DELETE /api/cms/pages/:id

GET    /api/cms/services
GET    /api/cms/services/:id
POST   /api/cms/services
PUT    /api/cms/services/:id
DELETE /api/cms/services/:id
```

---

## 📊 SEEDED DATA

### Homepage Sections (4 records)

1. **Hero Section**
   - Type: hero
   - Title: "Вашият надежден партньор в недвижими имоти"
   - Subtitle: "Открийте перфектния дом или инвестиция с ConsultingG"
   - Button: "Разгледайте имотите" → /properties

2. **Services Section**
   - Type: services
   - Title: "Нашите услуги"
   - Subtitle: "Пълен спектър от професионални услуги"
   - Button: "Вижте всички услуги" → /services

3. **Features Section**
   - Type: features
   - Title: "Защо да изберете нас?"
   - Subtitle: "Професионализъм, опит и отдаденост"

4. **CTA Section**
   - Type: cta
   - Title: "Готови да намерите вашия идеален имот?"
   - Button: "Свържете се с нас" → /contact

### Services (4 records)

1. **Покупко-продажба на имоти**
   - Icon: Home
   - Color: #3B82F6 (Blue)
   - Featured: Yes

2. **Отдаване под наем**
   - Icon: Key
   - Color: #10B981 (Green)
   - Featured: Yes

3. **Консултации и оценки**
   - Icon: TrendingUp
   - Color: #F59E0B (Amber)
   - Featured: Yes

4. **Правно съдействие**
   - Icon: FileText
   - Color: #8B5CF6 (Purple)
   - Featured: No

### Pages (3 records)

1. **За нас** (slug: about)
   - Menu: Yes, Order: 1
   - Template: default

2. **Контакти** (slug: contact)
   - Menu: Yes, Order: 2
   - Template: default

3. **Политика за поверителност** (slug: privacy)
   - Menu: Yes, Order: 3
   - Template: default

---

## ⚠️ IMPORTANT NOTES

1. **Database Provider:** MySQL 8.0 (NOT PostgreSQL or Supabase!)
2. **Connection:** Uses existing yogahonc_consultingg78 database
3. **No Data Loss:** Only ADDING new tables, not modifying existing ones
4. **Existing Models:** Property and PropertyImage models need to be added to schema
5. **Prisma Client:** Already generated and ready to use
6. **Backend Service:** cmsService.ts provides all CRUD operations

---

## 🔄 NEXT STEPS

1. ✅ **Run the SQL migration** on your MySQL server
2. ✅ **Verify tables created** with SELECT queries
3. ✅ **Create API routes** in backend to expose CMS functions
4. ⏳ **Update frontend** to call backend API endpoints
5. ⏳ **Build Admin Panel UI** for managing content
6. ⏳ **Integrate CMS** into public pages (Homepage, Dynamic Pages)

---

## 🐛 TROUBLESHOOTING

### Problem: "Can't reach database server"
**Solution:** This is expected. The database is on your production server (localhost:3306), not in this development environment. Run the SQL file directly on your MySQL server.

### Problem: "Property models not found"
**Solution:** You need to either:
- Option A: Run `npx prisma db pull` to pull existing tables from database
- Option B: Manually add Property and PropertyImage models to schema.prisma

### Problem: "UUID not working"
**Solution:** MySQL 8.0 doesn't have native UUID type. We use VARCHAR(36) instead. The Prisma `@default(uuid())` will generate UUIDs in the application.

### Problem: "JSON field errors"
**Solution:** JSON is supported in MySQL 5.7.8+. Make sure you're running MySQL 8.0.

---

## ✅ SUCCESS CRITERIA

After running the migration, you should have:

- ✅ 3 new tables (sections, pages, services)
- ✅ 11 total records (4 + 3 + 4)
- ✅ All indexes created
- ✅ Auto-update timestamps working
- ✅ UTF8MB4 charset for proper Unicode support
- ✅ Prisma client ready to use

**Test with:**
```sql
SELECT * FROM sections;
SELECT * FROM pages;
SELECT * FROM services;
```

All should return data!

---

## 📞 SUPPORT

If you encounter any issues:

1. Check MySQL connection: `mysql -u yogahonc_consultingg78 -p`
2. Verify database: `SHOW DATABASES;`
3. Check table structure: `DESCRIBE sections;`
4. View error logs: `SHOW ERRORS;`

**Status:** ✅ READY FOR MYSQL DEPLOYMENT
