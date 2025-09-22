# 🚀 ConsultingG Real Estate - Инструкции за Deploy в SuperHosting.bg

## 📋 Преглед на deployment

**Целева среда:** SuperHosting.bg cPanel/Apache с PHP 8.2+  
**Document Root:** `/home/yogahon/consultingg.com`  
**Домейн:** https://consultingg.com  
**База данни:** Supabase PostgreSQL (Cloud)  

## 📁 Файлове за качване

Качете всички файлове от папката `deploy-superhosting-final/` в document root:

```
/home/yogahon/consultingg.com/
├── index.html              # React SPA entry point
├── assets/                 # CSS, JS, fonts (от Vite build)
├── images/                 # Статични снимки на имоти
├── logo.png               # Лого на сайта
├── robots.txt             # SEO robots файл
├── sitemap.xml            # SEO sitemap
├── backend/               # PHP backend
│   ├── api/               # API файлове
│   │   ├── index.php     # API entry point
│   │   ├── controllers/  # Business logic
│   │   ├── models/       # Database модели
│   │   ├── routes/       # API routes
│   │   ├── config/       # Конфигурация
│   │   ├── middleware/   # Authentication
│   │   ├── utils/        # Utilities
│   │   ├── .env         # Environment variables
│   │   └── .htaccess    # API routing
│   └── .env             # Backend environment
├── uploads/              # User uploads (writable)
│   └── properties/       # Снимки на имоти (writable)
├── .htaccess            # Main Apache config
└── .user.ini            # PHP настройки
```

## 🚀 Стъпки за deployment

### **Стъпка 1: Качване на файлове (5 минути)**

1. **Login в cPanel** на вашия SuperHosting.bg акаунт
2. **Отвори File Manager** → Навигирай до `/home/yogahon/consultingg.com`
3. **Качи ZIP файл** → Създай ZIP от `deploy-superhosting-final/` папката
4. **Извади файловете** → Right-click ZIP → Extract → Extract to current directory
5. **Премести съдържанието** → Премести всички файлове от извадената папка в root директорията

### **Стъпка 2: PHP конфигурация (3 минути)**

1. **cPanel → PHP Selector**
2. **Избери PHP 8.2+**
3. **Включи Extensions:**
   - ✅ `pdo_pgsql` (PostgreSQL PDO driver)
   - ✅ `pgsql` (PostgreSQL функции)
   - ✅ `mbstring` (Multibyte string)
   - ✅ `fileinfo` (File information)
   - ✅ `json` (JSON функции)
   - ✅ `gd` (Image processing)

### **Стъпка 3: Права на файлове (2 минути)**

Задай правилните права чрез File Manager:

```bash
# Основни директории
chmod 755 /home/yogahon/consultingg.com
chmod 755 /home/yogahon/consultingg.com/backend
chmod 755 /home/yogahon/consultingg.com/backend/api
chmod 755 /home/yogahon/consultingg.com/uploads
chmod 775 /home/yogahon/consultingg.com/uploads/properties

# Конфигурационни файлове
chmod 644 /home/yogahon/consultingg.com/.env
chmod 644 /home/yogahon/consultingg.com/.htaccess
chmod 644 /home/yogahon/consultingg.com/backend/api/.htaccess
chmod 644 /home/yogahon/consultingg.com/backend/.env
chmod 644 /home/yogahon/consultingg.com/backend/api/.env
```

### **Стъпка 4: Тест на database връзката (2 минути)**

1. **Чрез браузър:** Посети `https://consultingg.com/backend/api/database/install.php`
2. **Очакван резултат:** Connection success с брой properties/images

### **Стъпка 5: SSL настройка (2 минути)**

1. **cPanel → SSL/TLS**
2. **Let's Encrypt SSL** → Добави за `consultingg.com`
3. **Force HTTPS Redirect** → Включи

## 🧪 Тестове за проверка

### **Frontend тестове:**
- [ ] **Homepage:** https://consultingg.com ✅
- [ ] **Properties:** https://consultingg.com/properties ✅
- [ ] **Property Detail:** Кликни на някой имот ✅
- [ ] **About Page:** https://consultingg.com/about ✅
- [ ] **Contact Page:** https://consultingg.com/contact ✅
- [ ] **Mobile Responsive:** Тествай на мобилно устройство ✅

### **API тестове:**
```bash
# Тест API health
curl https://consultingg.com/backend/api/health

# Тест properties endpoint
curl https://consultingg.com/backend/api/properties

# Тест services endpoint
curl https://consultingg.com/backend/api/services
```

### **Admin Panel тестове:**
- [ ] **Login:** https://consultingg.com/admin/login ✅
- [ ] **Credentials:** `georgiev@consultingg.com` / `PoloSport88*` ✅
- [ ] **Dashboard:** Виж статистики ✅
- [ ] **Properties List:** Виж всички имоти ✅
- [ ] **Add Property:** Създай нов имот ✅
- [ ] **Image Upload:** Тествай качване на снимки ✅
- [ ] **Edit Property:** Редактирай съществуващ имот ✅

## 🔧 Конфигурационни детайли

### **Database връзка (Supabase PostgreSQL):**
- **Host:** `db.mveeovfztfczibtvkpas.supabase.co`
- **Port:** `5432`
- **Database:** `postgres`
- **User:** `postgres`
- **Password:** `PoloSport88*`
- **SSL:** Required

### **Admin достъп:**
- **Email:** `georgiev@consultingg.com`
- **Password:** `PoloSport88*`
- **Role:** Administrator

### **File Upload:**
- **Max Size:** 20MB на файл
- **Allowed Types:** JPEG, PNG, WebP
- **Max Images:** 50 на имот
- **Storage:** `/home/yogahon/consultingg.com/uploads/properties/`

## 🚨 Troubleshooting

### **Чести проблеми:**

1. **500 Internal Server Error**
   - Провери PHP error logs в cPanel
   - Провери file permissions (755 за директории, 644 за файлове)
   - Увери се, че PHP extensions са включени

2. **Database Connection Failed**
   - Провери Supabase credentials в `backend/.env` и `backend/api/.env`
   - Провери дали `pdo_pgsql` extension е включен
   - Тествай връзката: `php backend/api/database/install.php`

3. **Images не се качват**
   - Провери `/uploads/properties/` permissions (775)
   - Провери PHP upload настройки в `.user.ini`
   - Провери наличното дисково пространство

4. **Admin Login проблеми**
   - Провери JWT secret в `.env` файловете
   - Провери admin credentials в Supabase database
   - Изчисти browser cache и cookies

5. **SPA Routes не работят**
   - Провери `.htaccess` rewrite rules
   - Провери дали mod_rewrite е включен
   - Увери се, че `index.html` съществува в document root

## ✅ Go-Live Checklist

### **Преди пускане:**
- [ ] Всички файлове качени в hosting
- [x] Supabase PostgreSQL database готова (не се нуждае от import)
- [ ] Environment variables конфигурирани
- [ ] File permissions зададени правилно
- [ ] SSL certificate активен
- [ ] Admin login работи
- [ ] Всички страници се зареждат правилно
- [ ] API endpoints отговарят
- [ ] Image upload работи
- [ ] Search функционалност работи
- [ ] Mobile responsiveness тестван

### **След пускане:**
- [ ] Monitor error logs
- [ ] Тествай contact form submissions
- [ ] Провери SEO meta tags
- [ ] Провери Google Analytics (ако е конфигуриран)
- [ ] Тествай performance с GTmetrix/PageSpeed
- [ ] Backup database и файлове

## 📞 Поддръжка

**SuperHosting.bg Support:** https://superhosting.bg/support  
**Supabase Dashboard:** https://supabase.com/dashboard  

---

**🎉 Deployment готов!**  
Вашият ConsultingG Real Estate website е готов за deployment на https://consultingg.com с Supabase PostgreSQL backend!

## 📦 Следващи стъпки

1. **Създай ZIP файл** от `deploy-superhosting-final/` папката
2. **Качи в SuperHosting.bg** чрез cPanel File Manager
3. **Извади файловете** в document root
4. **Задай file permissions** както е описано по-горе
5. **Тествай website-а** използвайки checklist-а по-горе

Проектът е напълно подготвен за production deployment в SuperHosting.bg!