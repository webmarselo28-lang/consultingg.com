# ConsultingG Real Estate - Production Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying ConsultingG Real Estate to SuperHosting.bg with cPanel hosting.

## Pre-Deployment Requirements

### Server Requirements
- **PHP Version**: 8.2 or higher
- **Required PHP Extensions**:
  - `pdo_pgsql` (PostgreSQL PDO driver)
  - `pgsql` (PostgreSQL functions)
  - `fileinfo` (File type detection)
  - `mbstring` (Multibyte string functions)
  - `intl` (Internationalization)
  - `gd` or `ImageMagick` (Image processing)
  - `exif` (Image metadata)
  - `curl` (HTTP requests)
  - `openssl` (Encryption)
  - `zip` (Archive handling)
  - `opcache` (PHP caching)

### Recommended PHP Settings
Add these to your `php.ini` or via cPanel:
```ini
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 30
date.timezone = Europe/Sofia
opcache.enable = On
opcache.enable_cli = On
```

## Deployment Steps

### Step 1: Set PHP Version and Extensions in cPanel

1. **Login to cPanel** on SuperHosting.bg
2. **Navigate to "PHP Selector"** (or "MultiPHP Manager")
3. **Set PHP Version** to 8.2 or higher
4. **Enable Required Extensions**:
   - Check all the extensions listed in the requirements above
   - Pay special attention to `pdo_pgsql` and `pgsql` for PostgreSQL support

### Step 2: Create PostgreSQL Database

1. **In cPanel, go to "PostgreSQL Databases"**
2. **Create Database**:
   - Database Name: `yogahonc_consultingg8`
   - Click "Create Database"
3. **Create Database User**:
   - Username: `yogahonc_consultingg88`
   - Generate a secure password (save it securely)
   - Click "Create User"
4. **Add User to Database**:
   - Select user: `yogahonc_consultingg88`
   - Select database: `yogahonc_consultingg8`
   - Grant "ALL PRIVILEGES"
   - Click "Make Changes"

### Step 3: Upload and Extract Files

1. **Upload `deploy.tar.gz`** to your cPanel File Manager
2. **Extract in `public_html/`**:
   ```bash
   # The contents should be extracted directly to public_html/
   # After extraction you should see:
   # public_html/index.html
   # public_html/assets/
   # public_html/backend/
   # public_html/uploads/
   ```
3. **Set Permissions**:
   - `public_html/uploads/` → 755 or 775 (writable)
   - `public_html/backend/api/` → 755
   - All `.htaccess` files → 644

### Step 4: Import Database

**Option A: Using phpPgAdmin (Recommended)**
1. **Open phpPgAdmin** from cPanel
2. **Connect** to database `yogahonc_consultingg8`
3. **Go to "SQL"** tab
4. **Upload and execute** the `dump_YYYYMMDD_HHMM.sql` file
5. **Verify** that all tables were created successfully

**Option B: Using SSH/psql (if available)**
```bash
# If SSH access is available
psql -h localhost -U yogahonc_consultingg88 -d yogahonc_consultingg8 < dump_YYYYMMDD_HHMM.sql
```

### Step 5: Configure Environment Variables

1. **Copy Environment File**:
   ```bash
   # In cPanel File Manager, navigate to public_html/backend/api/
   # Copy .env.production.example to .env in the same directory
   # Location: public_html/backend/api/.env
   ```

2. **Edit `.env` with Real Values**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-actual-domain.com
   
   DB_DRIVER=pgsql
   DB_HOST=localhost
   DB_PORT=5432
   DB_DATABASE=yogahonc_consultingg8
   DB_USERNAME=yogahonc_consultingg88
   DB_PASSWORD=your_actual_password_from_step2
   
   JWT_SECRET=generate_32_char_random_string_here
   ```

3. **Generate JWT Secret**:
   Use a tool like `openssl rand -base64 32` or generate online

### Step 6: Configure Domain and SSL

1. **Point Domain** to your SuperHosting account
2. **Enable SSL Certificate** in cPanel (Let's Encrypt recommended)
3. **Force HTTPS** by uncommenting the lines in `.htaccess`:
   ```apache
   RewriteCond %{HTTPS} !=on
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

## Post-Deploy Verification Checklist

Run these checks to ensure everything is working:

### 1. Health Check
```bash
curl https://your-domain.com/backend/api/health
# Expected: {"status":"ok","database":"connected","timestamp":"..."}
```

### 2. Frontend Loading
- Visit: `https://your-domain.com`
- Should load the React application
- Check browser console for any errors

### 3. Admin Login Test
1. Go to: `https://your-domain.com/admin/login`
2. Use your admin credentials (contact system administrator)
3. Should successfully log in to admin dashboard

### 4. Property Management Test
1. **Create Test Property**:
   - Go to admin → "Add Property"
   - Fill required fields and save
   - Should create folder `/uploads/properties/prop-XXX/`

2. **Image Upload Test**:
   - Upload property images
   - Images should be accessible via direct URL
   - Correct MIME types (image/jpeg, image/png)

3. **PDF Upload Test**:
   - Upload PDF documents
   - Should be downloadable with correct MIME type

### 5. Database Operations Test
1. **Featured Toggle**: Mark property as featured, verify persistence
2. **Admin Pagination**: Should show 10 properties per page
3. **Year Built Validation**: Test 1800-2040 range, NULL handling

### 6. API Response Test
```bash
# Test properties endpoint
curl https://your-domain.com/backend/api/properties | jq
# Should return valid JSON, no HTML leakage

# Test single property
curl https://your-domain.com/backend/api/properties/prop-001 | jq
# Should return property details with proper status codes
```

## Troubleshooting

### Common Issues and Solutions

**1. Database Connection Errors**
- Verify database credentials in `.env`
- Check PostgreSQL extensions are enabled
- Ensure database user has proper privileges

**2. File Upload Issues**
- Check `/uploads/` directory permissions (755/775)
- Verify `upload_max_filesize` and `post_max_size` settings
- Check disk space availability

**3. API Returning HTML Instead of JSON**
- Check `.htaccess` files are present and readable
- Verify `RewriteEngine On` is working
- Check PHP error logs for syntax errors

**4. Frontend Not Loading**
- Verify all assets copied to `public_html/`
- Check `.htaccess` SPA routing rules
- Enable browser developer tools to see 404 errors

**5. SSL/HTTPS Issues**
- Generate SSL certificate in cPanel
- Update `APP_URL` in `.env` to use HTTPS
- Clear browser cache after enabling SSL

## File Structure After Deployment

```
public_html/
├── index.html                 # React app entry point
├── assets/                    # Built CSS/JS assets
│   ├── index-*.css
│   ├── index-*.js
│   └── vendor-*.js
├── images/                    # Static images (logo, etc.)
├── backend/                   # PHP API
│   ├── .htaccess             # Backend security
│   └── api/                  # API files
│       ├── .env              # Environment config
│       ├── .htaccess         # API routing
│       ├── index.php         # API entry point
│       ├── vendor/           # Composer dependencies
│       ├── controllers/      # API controllers
│       ├── models/          # Data models
│       └── utils/           # Utility classes
├── uploads/                  # User uploaded files
│   ├── .htaccess            # Upload security
│   └── properties/          # Property images/docs
└── .htaccess               # Main routing & security
```

## Maintenance

### Regular Tasks
1. **Monitor Error Logs**: Check `/tmp/php-errors.log` and `/tmp/consultingg-errors.log`
2. **Database Backups**: Regular PostgreSQL dumps via cPanel
3. **Update Dependencies**: Periodic composer updates (test first)
4. **SSL Renewal**: Monitor SSL certificate expiration

### Security Updates
1. Keep PHP version updated
2. Monitor for composer security advisories
3. Review and update `.htaccess` security headers
4. Regular password updates for database and admin accounts

## Support

For technical issues:
1. Check error logs first
2. Verify all deployment steps completed
3. Test with the verification checklist
4. Contact SuperHosting.bg support for server-specific issues

---

**Deployment Package Generated**: `deploy.tar.gz`  
**Database Dump**: `dump_YYYYMMDD_HHMM.sql`  
**Environment Template**: `.env.production.example`

**IMPORTANT**: Never commit real passwords or API keys to version control. Always use the cPanel interface to set sensitive values.