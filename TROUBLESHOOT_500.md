# Troubleshooting 500 Error на consultingg.com

## 🔴 Проблем: API връща 500 Server Error

Видях от скрийншота:
```
Failed to load resource: the server responded with a status of 500
API error in getProperties: Error: Server error
```

## 🔍 Причини за 500 Error:

### 1. ❌ backend/.env файлът липсва или е грешен

**Решение:**
```bash
# В /home/yogahonc/consultingg.com/backend/
# Създайте .env файл с това съдържание:

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yogahonc_consultingg78
DB_USERNAME=yogahonc_consultingg78
DB_PASSWORD=PoloSport88*
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

APP_ENV=production
APP_DEBUG=false
APP_URL=https://consultingg.com
PUBLIC_BASE_URL=https://consultingg.com

JWT_SECRET=consultingg-jwt-secret-key-2024
JWT_AUD=consultingg.com
```

### 2. ❌ Database connection проблем

**Проверка:**
```bash
# SSH в SuperHosting:
mysql -u yogahonc_consultingg78 -p yogahonc_consultingg78
# Password: PoloSport88*

# Ако работи, проверете таблиците:
SHOW TABLES;
SELECT COUNT(*) FROM properties;
SELECT COUNT(*) FROM property_images;
```

### 3. ❌ PHP Extensions не са enabled

**Решение в cPanel:**
1. Select PHP Version → Extensions
2. Активирайте:
   - ✅ pdo_mysql
   - ✅ mysqli
   - ✅ json
   - ✅ mbstring
   - ✅ fileinfo

### 4. ❌ Composer dependencies липсват

**Решение:**
```bash
# В /home/yogahonc/consultingg.com/backend/
composer install --no-dev --optimize-autoloader
```

### 5. ❌ File permissions проблеми

**Решение:**
```bash
chmod 644 backend/.env
chmod 755 backend/
chmod 755 backend/api/
chmod 644 backend/api/index.php
```

## 🧪 Диагностика

### Стъпка 1: Test Connection
Отворете в браузър:
```
https://consultingg.com/backend/test_connection.php
```

Това ще покаже:
- ✅/❌ .env file exists
- ✅/❌ Database connection
- ✅/❌ PHP extensions
- Properties count
- Images count

### Стъпка 2: Check Apache Error Log

**В cPanel → Error Log или SSH:**
```bash
tail -f /home/yogahonc/logs/consultingg.com-error_log
```

Това ще покаже точната PHP грешка!

### Стъпка 3: Enable Debug Mode

**Временно в backend/.env:**
```env
APP_DEBUG=true
```

Refresh страницата - ще видите детайлна грешка вместо 500!

### Стъпка 4: Test API Direct

```bash
curl -v https://consultingg.com/backend/api/properties
```

Или в браузър:
```
https://consultingg.com/backend/api/properties
```

## ✅ Най-вероятното решение:

**backend/.env файлът липсва!**

1. SSH или cPanel File Manager
2. Отидете в `/home/yogahonc/consultingg.com/backend/`
3. Създайте `.env` файл (note: започва с точка!)
4. Копирайте съдържанието от `.env.example.consultingg`
5. Запазете файла
6. Проверете permissions: `chmod 644 .env`
7. Refresh страницата

## 📋 Checklist

- [ ] backend/.env файл съществува и има правилни MySQL credentials
- [ ] Database connection работи (test с mysql command line)
- [ ] PHP extensions enabled (pdo_mysql, mysqli, json, mbstring)
- [ ] Composer dependencies installed
- [ ] File permissions правилни (644 за .env, 755 за directories)
- [ ] Apache може да чете .env файла (не е блокиран от .htaccess)

## 🎯 Expected Result

След поправка:
```
✅ https://consultingg.com/backend/test_connection.php
   → Shows "MySQL Connection Successful!"
   → Shows "Properties in database: X"

✅ https://consultingg.com/api/properties
   → Returns JSON with properties

✅ https://consultingg.com/
   → Loads homepage with images! 🎉
```

---

**Първо проверете дали backend/.env съществува - това е най-честата причина за 500 грешка!**
