# 🚀 Production Deployment Guide - ConsultingG Real Estate

**Target Domain:** goro.consultingg.com (staging) → consultingg.com (production)
**Database:** yogahonc_consultingg788 (MySQL 8.0)
**Server User:** webmarselo28-lang
**Total Time:** ~2 hours

---

## 📋 PRE-DEPLOYMENT CHECKLIST

- [ ] SSH access to server
- [ ] MySQL root password available
- [ ] Domain DNS configured (goro.consultingg.com)
- [ ] Backend code downloaded from project
- [ ] Frontend built (`npm run build` completed)
- [ ] Backup of existing data (if any)

---

## PHASE 1: DATABASE SETUP (25 minutes)

### Step 1.1: Create New Database (5 min)

```bash
# SSH to server
ssh webmarselo28-lang@your-server

# Login to MySQL
mysql -u root -p

# Create database with UTF-8 support
CREATE DATABASE yogahonc_consultingg788
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'yogahonc_consultingg788'@'localhost'
  IDENTIFIED BY 'PoloSport88*';

# Grant all privileges
GRANT ALL PRIVILEGES ON yogahonc_consultingg788.*
  TO 'yogahonc_consultingg788'@'localhost';

FLUSH PRIVILEGES;

# Verify database created
SHOW DATABASES LIKE 'yogahonc_consultingg788';

EXIT;
```

**Expected Output:**
```
+-----------------------------+
| Database                    |
+-----------------------------+
| yogahonc_consultingg788     |
+-----------------------------+
```

---

### Step 1.2: Copy Old Database (10 min)

```bash
# Backup old database (IMPORTANT!)
mysqldump -u yogahonc_consultingg78 \
  -p'PoloSport88*' \
  yogahonc_consultingg78 \
  > /tmp/old_db_backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh /tmp/old_db_backup*.sql

# Import to new database
mysql -u yogahonc_consultingg788 \
  -p'PoloSport88*' \
  yogahonc_consultingg788 \
  < /tmp/old_db_backup_*.sql
```

**This copies ALL existing data:**
- ✅ properties table (~50 properties)
- ✅ property_images table (~200+ images)
- ✅ users table
- ✅ documents table (if exists)
- ✅ services table (if exists)

---

### Step 1.3: Run CMS Migration (10 min)

**Download migration file:** `backend/prisma/migrations/add_cms_models.sql`

```bash
# Upload migration to server
scp backend/prisma/migrations/add_cms_models.sql \
  webmarselo28-lang@server:/tmp/

# On server, run migration (adds CMS tables)
mysql -u yogahonc_consultingg788 \
  -p'PoloSport88*' \
  yogahonc_consultingg788 \
  < /tmp/add_cms_models.sql
```

**This adds:**
- ✅ sections table (CMS content sections)
- ✅ pages table (CMS static pages)
- ✅ Seed data (4 sections, 3 pages)

---

### Step 1.4: Verify Database (5 min)

```sql
mysql -u yogahonc_consultingg788 -p'PoloSport88*' yogahonc_consultingg788

-- Check all tables exist
SHOW TABLES;

-- Expected output:
-- +--------------------------------------+
-- | Tables_in_yogahonc_consultingg788   |
-- +--------------------------------------+
-- | documents                            |
-- | pages                                | ← NEW
-- | properties                           |
-- | property_images                      |
-- | sections                             | ← NEW
-- | services                             |
-- | users                                |
-- +--------------------------------------+

-- Verify old data copied
SELECT COUNT(*) as properties_count FROM properties;
SELECT COUNT(*) as images_count FROM property_images;
SELECT COUNT(*) as users_count FROM users;

-- Verify CMS seed data
SELECT COUNT(*) as sections_count FROM sections;  -- Should be 4
SELECT COUNT(*) as pages_count FROM pages;        -- Should be 3

-- View sample property
SELECT id, property_code, title, price, city_region FROM properties LIMIT 3;

-- View CMS sections
SELECT id, page_slug, type, title FROM sections ORDER BY sort_order;

EXIT;
```

---

## PHASE 2: BACKEND DEPLOYMENT (30 minutes)

### Step 2.1: Prepare Deployment Package (5 min)

**On your local machine or download from project:**

```bash
# Files needed:
backend/
├── src/               # All TypeScript source files
├── prisma/            # Schema and migrations
├── package.json       # Dependencies
├── tsconfig.json      # TypeScript config
└── .env.example       # Environment template

# Create deployment package
cd backend
tar -czf backend-deployment.tar.gz \
  src/ \
  prisma/ \
  package.json \
  tsconfig.json \
  .env.example

# Verify package
tar -tzf backend-deployment.tar.gz | head -20
```

---

### Step 2.2: Upload to Server (5 min)

```bash
# Upload package
scp backend-deployment.tar.gz \
  webmarselo28-lang@server:/home/webmarselo28-lang/

# SSH to server
ssh webmarselo28-lang@server

# Create backend directory
mkdir -p /home/webmarselo28-lang/goro.consultingg.com/backend
cd /home/webmarselo28-lang/goro.consultingg.com/backend

# Extract package
tar -xzf ~/backend-deployment.tar.gz

# Verify extraction
ls -la
# Should see: src/ prisma/ package.json tsconfig.json
```

---

### Step 2.3: Configure Environment (5 min)

```bash
cd /home/webmarselo28-lang/goro.consultingg.com/backend

# Generate secure JWT secret
JWT_SECRET="consultingg-production-jwt-secret-2025-$(openssl rand -hex 16)"

# Create production .env file
cat > .env << EOF
NODE_ENV=production
PORT=3000

# Database (NEW database!)
DATABASE_URL="mysql://yogahonc_consultingg788:PoloSport88*@localhost:3306/yogahonc_consultingg788"

# Public URLs
PUBLIC_BASE_URL=https://goro.consultingg.com

# JWT Authentication
JWT_SECRET=${JWT_SECRET}
JWT_EXPIRES_IN=7d
JWT_AUD=consultingg.com

# File Uploads
UPLOADS_DIR=/home/webmarselo28-lang/goro.consultingg.com/uploads
MAX_FILE_SIZE=20971520
UPLOAD_ALLOWED_TYPES=image/jpeg,image/jpg,image/png,image/webp,application/pdf

# CORS Origins
CORS_ORIGIN=https://goro.consultingg.com,https://consultingg.com,https://www.consultingg.com

# Logging
LOG_LEVEL=info
LOG_DIR=./logs

# Rate Limiting
RATE_LIMIT_MAX_REQUESTS=100
AUTH_RATE_LIMIT_MAX=5
EOF

# Verify .env created
cat .env

# Create logs directory
mkdir -p logs
```

---

### Step 2.4: Install & Build (10 min)

```bash
cd /home/webmarselo28-lang/goro.consultingg.com/backend

# Install production dependencies
npm ci --production

# Install dev dependencies for build
npm install --save-dev typescript @types/node ts-node prisma

# Generate Prisma client
npx prisma generate

# Build TypeScript to JavaScript
npm run build

# Verify build succeeded
ls -la dist/
# Expected: server.js, config/, controllers/, services/, etc.

# Test database connection
node -e "
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();
prisma.\$connect()
  .then(() => {
    console.log('✅ Database connected successfully!');
    return prisma.property.count();
  })
  .then(count => console.log('✅ Properties in database:', count))
  .catch(err => console.error('❌ Connection failed:', err))
  .finally(() => prisma.\$disconnect());
"
```

**Expected Output:**
```
✅ Database connected successfully!
✅ Properties in database: 50
```

---

### Step 2.5: Start with PM2 (5 min)

```bash
# Install PM2 globally (if not already installed)
npm install -g pm2

# Start backend with PM2
pm2 start dist/server.js \
  --name consultingg-backend \
  --node-args="--max-old-space-size=512" \
  --env production

# Save PM2 process list
pm2 save

# Setup PM2 to start on system boot
pm2 startup

# Check status
pm2 status

# View logs
pm2 logs consultingg-backend --lines 50
```

**Expected in logs:**
```
✅ Database connected successfully
🚀 Backend API running on port 3000
📍 Environment: production
🌐 Public URL: https://goro.consultingg.com
📁 Uploads directory: /home/.../uploads
🔒 CORS Origins: https://goro.consultingg.com,...
```

**PM2 Useful Commands:**
```bash
pm2 status                    # Check status
pm2 logs consultingg-backend  # View logs
pm2 restart consultingg-backend  # Restart
pm2 stop consultingg-backend  # Stop
pm2 delete consultingg-backend   # Remove
```

---

## PHASE 3: FRONTEND DEPLOYMENT (15 minutes)

### Step 3.1: Update Frontend Config (5 min)

**In project, update `src/services/api.ts`:**

```typescript
// src/services/api.ts
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL
  || 'https://goro.consultingg.com/api';

export default axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json'
  },
  withCredentials: true
});
```

**Create `.env.production` in project root:**

```env
VITE_API_BASE_URL=https://goro.consultingg.com/api
```

---

### Step 3.2: Build Frontend (5 min)

**In project directory:**

```bash
# Build for production
npm run build

# Verify build output
ls -la dist/
# Expected: index.html, assets/, robots.txt, sitemap.xml

# Check bundle sizes
du -sh dist/assets/*
```

**Expected Build Output:**
```
✓ 1514 modules transformed
✓ built in 4.53s
dist/index.html: 3.61 kB / 1.37 kB gzipped
dist/assets/index.js: 303.30 kB / 79.43 kB gzipped
```

---

### Step 3.3: Upload to Server (5 min)

```bash
# From your local machine:
cd /path/to/project

# Create tarball of dist folder
tar -czf frontend-dist.tar.gz dist/

# Upload to server
scp frontend-dist.tar.gz \
  webmarselo28-lang@server:/home/webmarselo28-lang/

# SSH to server
ssh webmarselo28-lang@server

# Navigate to web root
cd /home/webmarselo28-lang/goro.consultingg.com/

# Extract frontend
tar -xzf ~/frontend-dist.tar.gz

# Copy uploads folder from old location (if exists)
# If you have existing property images:
# cp -r /path/to/old/uploads ./uploads

# OR create new uploads directory
mkdir -p uploads/properties

# Set proper permissions
chmod -R 755 dist/
chmod -R 755 uploads/
chown -R webmarselo28-lang:webmarselo28-lang dist/
chown -R webmarselo28-lang:webmarselo28-lang uploads/

# Verify structure
ls -la
# Expected:
# drwxr-xr-x backend/
# drwxr-xr-x dist/
# drwxr-xr-x uploads/
```

---

## PHASE 4: WEB SERVER CONFIG (15 minutes)

### Step 4.1: Nginx Configuration

```bash
# Create nginx config
sudo nano /etc/nginx/sites-available/goro.consultingg.com
```

**Nginx Configuration:**

```nginx
# goro.consultingg.com - ConsultingG Real Estate
server {
    listen 80;
    server_name goro.consultingg.com;

    # Web root
    root /home/webmarselo28-lang/goro.consultingg.com/dist;
    index index.html;

    # Logging
    access_log /var/log/nginx/goro.consultingg.com.access.log;
    error_log /var/log/nginx/goro.consultingg.com.error.log;

    # Frontend (React SPA)
    location / {
        try_files $uri $uri/ /index.html;

        # Cache static files
        location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }

    # Backend API
    location /api/ {
        proxy_pass http://localhost:3000/api/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    # Static uploads (property images, PDFs)
    location /uploads/ {
        alias /home/webmarselo28-lang/goro.consultingg.com/uploads/;
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Access-Control-Allow-Origin "*";

        # Enable directory listing for debugging (remove in production)
        # autoindex on;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript
               application/json application/javascript application/xml+rss
               application/rss+xml font/truetype font/opentype
               application/vnd.ms-fontobject image/svg+xml;

    # Client max body size (for file uploads)
    client_max_body_size 20M;
}
```

---

### Step 4.2: Enable Site & Test

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/goro.consultingg.com \
           /etc/nginx/sites-enabled/

# Test nginx configuration
sudo nginx -t

# Expected output:
# nginx: configuration file /etc/nginx/nginx.conf test is successful

# Reload nginx
sudo systemctl reload nginx

# Check nginx status
sudo systemctl status nginx

# View nginx error log (if issues)
sudo tail -f /var/log/nginx/goro.consultingg.com.error.log
```

---

### Step 4.3: SSL/HTTPS with Certbot (Optional but Recommended)

```bash
# Install Certbot (if not already installed)
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d goro.consultingg.com

# Follow prompts:
# - Enter email
# - Agree to terms
# - Choose: Redirect HTTP to HTTPS (option 2)

# Test auto-renewal
sudo certbot renew --dry-run

# Certificate will auto-renew every 90 days
```

---

## PHASE 5: TESTING & VERIFICATION (30 minutes)

### Step 5.1: Backend Health Checks

```bash
# 1. Test backend health endpoint
curl -I https://goro.consultingg.com/api/health
# Expected: HTTP/2 200

curl https://goro.consultingg.com/api/health | jq '.'
# Expected:
# {
#   "success": true,
#   "status": "healthy",
#   "database": "connected",
#   "uptime": 123.45
# }

# 2. Test root API endpoint
curl https://goro.consultingg.com/api/ | jq '.'
# Expected:
# {
#   "success": true,
#   "name": "ConsultingG Real Estate API",
#   "version": "1.0.0"
# }

# 3. Test properties API
curl https://goro.consultingg.com/api/properties | jq '.data[0]'
# Expected: Property object with images array

# 4. Test sections API (CMS)
curl https://goro.consultingg.com/api/cms/sections | jq '.'
# Expected: Array of 4 sections

# 5. Test pages API (CMS)
curl https://goro.consultingg.com/api/cms/pages | jq '.'
# Expected: Array of 3 pages

# 6. Test image loading
curl -I https://goro.consultingg.com/uploads/properties/prop-001/image.jpg
# Expected: HTTP/2 200 (if file exists)
```

---

### Step 5.2: Frontend Testing

**Open in browser: `https://goro.consultingg.com`**

**Manual Test Checklist:**

- [ ] ✅ Homepage loads without errors
- [ ] ✅ Properties list displays with images
- [ ] ✅ Property detail page works (click on a property)
- [ ] ✅ Search functionality works (try filtering)
- [ ] ✅ All images load correctly
- [ ] ✅ Navigation works (click all menu items)
- [ ] ✅ Contact page loads
- [ ] ✅ Services page shows CMS content
- [ ] ✅ Mobile responsive (test on phone or dev tools)
- [ ] ✅ No console errors (F12 → Console)
- [ ] ✅ Network tab shows API calls successful

---

### Step 5.3: Admin Panel Testing

**Admin Login: `https://goro.consultingg.com/admin/login`**

**Test Admin Features:**

1. **Login:**
   - [ ] Admin login page loads
   - [ ] Can login with credentials
   - [ ] Redirects to dashboard

2. **Properties Management:**
   - [ ] Can view properties list
   - [ ] Can edit a property
   - [ ] Can upload images
   - [ ] Changes save successfully

3. **CMS Management:**
   - [ ] Can view/edit sections
   - [ ] Can view/edit pages
   - [ ] Can view/edit services
   - [ ] Changes persist

---

### Step 5.4: Performance Testing

```bash
# Test page load speed
curl -o /dev/null -s -w "Time: %{time_total}s\nSize: %{size_download} bytes\n" \
  https://goro.consultingg.com

# Expected: < 2 seconds

# Test concurrent requests
ab -n 100 -c 10 https://goro.consultingg.com/api/properties
# Expected: No errors, consistent response times

# Check gzip compression
curl -H "Accept-Encoding: gzip" -I https://goro.consultingg.com | grep -i encoding
# Expected: Content-Encoding: gzip
```

---

### Step 5.5: Log Monitoring

```bash
# Backend logs
pm2 logs consultingg-backend --lines 100

# Nginx access log
sudo tail -f /var/log/nginx/goro.consultingg.com.access.log

# Nginx error log
sudo tail -f /var/log/nginx/goro.consultingg.com.error.log

# Check for errors
pm2 logs consultingg-backend --err --lines 50
```

---

## 🎯 SUCCESS CRITERIA

### All Systems Green ✅

- [x] **Database:** yogahonc_consultingg788 created with all tables
- [x] **Backend:** Running on PM2, port 3000, no errors
- [x] **Frontend:** Deployed to dist/, accessible via domain
- [x] **Nginx:** Configured, proxying API, serving static files
- [x] **SSL:** HTTPS enabled with valid certificate
- [x] **APIs:** All endpoints responding with valid data
- [x] **Images:** Property images loading correctly
- [x] **Admin:** Login works, can manage content
- [x] **Logs:** No errors in PM2 or nginx logs
- [x] **Performance:** Page load < 2s, API response < 500ms

---

## 🔧 TROUBLESHOOTING

### Backend won't start

```bash
# Check logs
pm2 logs consultingg-backend --err --lines 50

# Common issues:
# 1. Database connection failed
#    → Verify DATABASE_URL in backend/.env
#    → Test: mysql -u yogahonc_consultingg788 -p

# 2. Port 3000 already in use
#    → Check: lsof -i :3000
#    → Kill process or change PORT in .env

# 3. Missing node_modules
#    → Run: npm ci --production

# Restart backend
pm2 restart consultingg-backend
```

---

### Frontend shows 404

```bash
# Check nginx config
sudo nginx -t

# Verify frontend files exist
ls -la /home/webmarselo28-lang/goro.consultingg.com/dist/

# Check nginx error log
sudo tail -f /var/log/nginx/goro.consultingg.com.error.log

# Reload nginx
sudo systemctl reload nginx
```

---

### API calls fail (CORS errors)

```bash
# Check CORS_ORIGIN in backend/.env
cat /home/webmarselo28-lang/goro.consultingg.com/backend/.env | grep CORS

# Should include your domain:
# CORS_ORIGIN=https://goro.consultingg.com,...

# Restart backend after .env changes
pm2 restart consultingg-backend
```

---

### Images don't load

```bash
# Check uploads directory exists
ls -la /home/webmarselo28-lang/goro.consultingg.com/uploads/

# Check permissions
sudo chown -R webmarselo28-lang:webmarselo28-lang uploads/
chmod -R 755 uploads/

# Test direct image access
curl -I https://goro.consultingg.com/uploads/properties/prop-001/test.jpg

# Check nginx config for /uploads/ location
sudo nginx -t
```

---

### Database connection errors

```bash
# Test database connection
mysql -u yogahonc_consultingg788 -p'PoloSport88*' yogahonc_consultingg788

# Check tables exist
SHOW TABLES;

# Verify DATABASE_URL in .env matches exactly
cat backend/.env | grep DATABASE_URL

# Test from Node.js
cd backend
node -e "const {PrismaClient}=require('@prisma/client'); const p=new PrismaClient(); p.\$connect().then(()=>console.log('OK')).catch(console.error).finally(()=>p.\$disconnect());"
```

---

## 📞 SUPPORT & NEXT STEPS

### Post-Deployment Tasks

1. **Set up monitoring:**
   ```bash
   # Install monitoring tools
   npm install -g pm2-logrotate
   pm2 install pm2-logrotate
   ```

2. **Configure backups:**
   ```bash
   # Daily database backup cron
   crontab -e
   # Add: 0 2 * * * mysqldump -u yogahonc_consultingg788 -p'PoloSport88*' yogahonc_consultingg788 > /backups/db_$(date +\%Y\%m\%d).sql
   ```

3. **Monitor disk space:**
   ```bash
   df -h
   du -sh /home/webmarselo28-lang/goro.consultingg.com/*
   ```

4. **Update to production domain:**
   - Test thoroughly on goro.consultingg.com
   - Update all URLs to consultingg.com
   - Update DNS records
   - Obtain new SSL certificate for consultingg.com

---

## ✅ DEPLOYMENT COMPLETE!

**Your application is now live at:**
- 🌐 **Frontend:** https://goro.consultingg.com
- 🔌 **API:** https://goro.consultingg.com/api
- 🔒 **Admin:** https://goro.consultingg.com/admin/login

**Useful Commands:**
```bash
# Backend
pm2 status
pm2 logs consultingg-backend
pm2 restart consultingg-backend

# Nginx
sudo systemctl status nginx
sudo systemctl reload nginx
sudo tail -f /var/log/nginx/goro.consultingg.com.error.log

# Database
mysql -u yogahonc_consultingg788 -p
```

**Documentation:**
- Backend Diagnostic: `BACKEND_DIAGNOSTIC_REPORT.md`
- Build Status: `BUILD_STATUS_REPORT.md`
- CMS Guide: `CMS_IMPLEMENTATION_SUMMARY.md`
- Environment Config: `backend/.env.example`

---

**Deployment Guide v1.0**
**Date:** 2025-01-24
**Status:** ✅ Production Ready
