# 🚀 ConsultingG Real Estate - Deployment Guide за superhosting.bg

## 📋 Преглед на проекта

**ConsultingG Real Estate** е модерна платформа за недвижими имоти с React frontend и PHP backend, готова за deployment на superhosting.bg.

## 🎯 Production спецификации

### **Хостинг детайли:**
- **Provider:** superhosting.bg
- **Domain:** consultingg.com
- **SSL:** Задължителен (HTTPS)
- **PHP:** 8.0+ 
- **MySQL:** 8.0+

### **Database конфигурация:**
- **Host:** localhost
- **Database:** yogahonc_consultingg55
- **User:** yogahonc_consultingg55
- **Password:** PoloSport88*
- **Collation:** utf8mb4_general_ci

### **Admin достъп:**
- **Email:** georgiev@consultingg.com
- **Password:** PoloSport88*
- **Role:** Administrator

## 📁 Файлове за качване

### **Структура на файловете:**
```
public_html/
├── index.html              # Frontend entry point
├── assets/                 # CSS, JS, fonts (от build)
├── images/                 # Статични снимки
├── backend/               # PHP API
│   ├── api/               # API endpoints
│   ├── config/            # Конфигурация
│   ├── controllers/       # API контролери
│   ├── models/            # Database модели
│   ├── middleware/        # Middleware функции
│   ├── routes/            # API routes
│   ├── utils/             # Utilities
│   └── vendor/            # Composer dependencies
├── uploads/               # Качени снимки
│   └── properties/        # Снимки на имоти (по папки)
├── .htaccess             # Apache rewrite rules
├── .env                  # Environment config (от .env.production)
└── composer.json         # PHP dependencies
```

## 🚀 Deployment стъпки

### **Стъпка 1: Database Setup (5 мин)**
1. **Login в cPanel** на superhosting.bg
2. **Отвори phpMyAdmin**
3. **Избери database:** `yogahonc_consultingg55`
4. **Import tab** → Избери файл: `backend/database/yogahonc_consultingg55.sql`
5. **Кликни "Go"** за импортиране

### **Стъпка 2: Environment Configuration (2 мин)**
1. **Преименувай** `.env.production` → `.env`
2. **Провери** database данните в .env файла

### **Стъпка 3: File Permissions (2 мин)**
```bash
# Задай права на папките:
uploads/ → 755
uploads/properties/ → 755  
backend/ → 755
.env → 644
```

### **Стъпка 4: Composer Dependencies (3 мин)**
```bash
# В hosting file manager или SSH:
cd backend/
composer install --no-dev --optimize-autoloader
```
*(или качи vendor/ папката ако няма SSH достъп)*

### 5. File Permissions (2 мин)**
```bash
# Задай права на папките:
chmod 755 backend/uploads/
chmod 755 backend/uploads/properties/
```

### **Стъпка 6: SSL Setup (2 мин)**
1. **cPanel** → SSL/TLS
2. **Активирай Let's Encrypt** за consultingg.com
3. **Включи "Force HTTPS Redirect"**

## 🧪 Image Upload Testing

### **API Endpoints:**
```bash
# Upload images to property
POST /api/properties/{id}/images (multipart, images[]) → JSON с id/url/path

# Set main image
PUT /api/properties/{id}/images/{imageId}/main → {success:true}

# Delete image
DELETE /api/properties/{id}/images/{imageId} → {success:true}
```

### **Smoke Test:**
- [ ] **GET /api/health** → JSON response ✅
- [ ] **Upload images** → Files saved in backend/uploads/properties/{id}/ ✅
- [ ] **Set main image** → is_main=1 for selected, is_main=0 for others ✅
- [ ] **Delete image** → File and DB record removed ✅
- [ ] **Property details** → Shows all images, one marked as main ✅

## 🧪 Testing Checklist

### **Frontend тестове:**
- [ ] **Homepage:** https://consultingg.com ✅
- [ ] **Properties page:** https://consultingg.com/properties ✅
- [ ] **Property details:** Кликни на имот ✅
- [ ] **About page:** https://consultingg.com/about ✅
- [ ] **Contact page:** https://consultingg.com/contact ✅
- [ ] **Search functionality:** Тествай търсенето ✅
- [ ] **Mobile responsive:** Тествай на мобилен ✅

### **Backend API тестове:**
```bash
# Тествай публичните endpoints:
curl https://consultingg.com/backend/api/properties
curl https://consultingg.com/backend/api/services
curl https://consultingg.com/backend/api/pages

# Тествай authentication:
curl -X POST "https://consultingg.com/backend/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "georgiev@consultingg.com", "password": "PoloSport88*"}'
```

### **Admin Panel тестове:**
- [ ] **Login page:** https://consultingg.com/admin/login ✅
- [ ] **Login с:** georgiev@consultingg.com / PoloSport88* ✅
- [ ] **Dashboard:** Виж статистики ✅
- [ ] **Properties list:** Виж всички имоти ✅
- [ ] **Add property:** Създай нов имот ✅
- [ ] **Image upload:** Тествай качване на снимки ✅
- [ ] **Edit property:** Редактирай съществуващ имот ✅

## 🔧 Конфигурационни файлове

### **Environment (.env):**
```env
# Production Database
DB_HOST=localhost
DB_NAME=yogahonc_consultingg55
DB_USER=yogahonc_consultingg55
DB_PASS=PoloSport88*

# JWT Configuration
JWT_SECRET=consultingg-production-jwt-secret-key-2024-change-this-immediately
JWT_AUD=consultingg.com

# Upload Configuration
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=image/jpeg,image/jpg,image/png,image/webp
MAX_IMAGES_PER_PROPERTY=50

# Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://consultingg.com

# API Configuration
VITE_API_URL=https://consultingg.com/backend/api
```

### **Apache (.htaccess):**
- **Root .htaccess** - React Router support + API routing
- **Backend .htaccess** - API endpoint routing + security

## 🛡️ Security Features

### **Implemented Security:**
- ✅ **JWT Authentication** за admin достъп
- ✅ **SQL Injection Protection** чрез prepared statements
- ✅ **XSS Protection** чрез input validation
- ✅ **File Upload Security** - ограничени типове и размери
- ✅ **CORS Configuration** за API endpoints
- ✅ **HTTPS Redirect** в production
- ✅ **Sensitive Files Protection** (.env, .sql, .md файлове)

### **File Upload Security:**
- Максимум 10MB на файл
- Позволени формати: JPEG, PNG, WebP
- Максимум 50 снимки на имот
- Всеки имот има собствена папка за снимки

## 📊 Database Schema

### **Основни таблици:**
- **users** - Admin потребители
- **properties** - Имоти с всички характеристики
- **property_images** - Снимки на имоти (до 50 на имот)
- **services** - Конфигурируеми услуги
- **pages** - Динамично съдържание (About, Contact)

### **Indexes за Performance:**
- Всички търсени полета са индексирани
- Foreign key constraints за data integrity
- Optimized queries за бързо търсене

## 🚨 Troubleshooting

### **Чести проблеми:**

1. **500 Internal Server Error**
   - Провери PHP error logs в cPanel
   - Провери file permissions
   - Провери .htaccess syntax

2. **Database Connection Failed**
   - Провери database credentials в .env
   - Провери дали database съществува
   - Тествай connection от phpMyAdmin

3. **Images Not Loading**
   - Провери uploads/ directory permissions (755)
   - Провери image paths в database
   - Провери file upload limits

4. **Admin Login Issues**
   - Провери admin credentials в database
   - Провери JWT secret в .env
   - Clear browser cache

5. **API Not Working**
   - Провери .htaccess rewrite rules
   - Провери API endpoints
   - Провери CORS headers

## ✅ Go-Live Checklist

### **Pre-Launch:**
- [ ] Database импортирана успешно
- [ ] Всички файлове качени на hosting
- [ ] Environment variables конфигурирани
- [ ] SSL certificate активен
- [ ] Admin login работи
- [ ] Всички страници се зареждат
- [ ] API endpoints отговарят
- [ ] Image upload работи
- [ ] Search functionality работи
- [ ] Mobile responsiveness тестван

### **Post-Launch:**
- [ ] Monitor error logs
- [ ] Тествай всички функционалности
- [ ] Setup regular backups
- [ ] Monitor site performance
- [ ] Submit to search engines

## 📞 Support

### **За проблеми:**
1. **Провери error logs** в cPanel първо
2. **Тествай API endpoints** с curl
3. **Провери database connections**
4. **Свържи се с superhosting.bg support** за server проблеми

---

## 🎉 Deployment Complete!

**Live URLs:**
- **Website:** https://consultingg.com
- **Admin Panel:** https://consultingg.com/admin/login
- **API:** https://consultingg.com/backend/api

**Admin Access:**
- **Email:** georgiev@consultingg.com
- **Password:** PoloSport88*

## 📊 Database

**On SuperHosting (MySQL):** run `backend/sql/migrations/2025-09-01_property_images_mysql.sql` in phpMyAdmin.

**Do not run the PostgreSQL migration in production.**

Проектът е готов за production с всички функционалности:
✅ Property search and listings
✅ Admin panel за content management
✅ Image upload system с папки за всеки имот
✅ Responsive design
✅ SEO optimization
✅ Security measures implemented