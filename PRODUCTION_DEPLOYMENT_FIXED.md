# 🚀 ConsultingG Real Estate - Production Deployment Guide (FIXED)

**Status:** ✅ All Issues Resolved
**Domain:** https://goro.consultingg.com
**Database:** yogahonc_consultingg788 (MySQL 8.0)
**Hosting:** SuperHosting.bg (cPanel + Node.js Selector)
**Server Path:** /home/yogahonc/goro.consultingg.com/

---

## 🎯 What Was Fixed

### Critical Issues Resolved ✅

1. **API 500 Errors** - Fixed database connection and environment configuration
2. **Images Not Loading** - Fixed absolute paths and static file serving
3. **Database Configuration** - Correct MySQL connection string
4. **File Structure** - Proper separation of backend and frontend
5. **Admin Login** - Working authentication with JWT tokens
6. **CORS Errors** - Proper origin configuration
7. **Upload Paths** - Absolute paths for cPanel environment

---

## 📁 Required File Structure

```
/home/yogahonc/goro.consultingg.com/
├── backend/                          # Node.js backend application
│   ├── src/                          # TypeScript source files
│   ├── dist/                         # Compiled JavaScript (after build)
│   ├── prisma/                       # Database schema
│   ├── node_modules/                 # Dependencies
│   ├── package.json
│   ├── tsconfig.json
│   ├── .env                          # COPY from backend/.env.production
│   └── logs/                         # Application logs
│
└── public_html/                      # Frontend + static files
    ├── index.html                    # React app entry point
    ├── assets/                       # JS, CSS bundles
    ├── .htaccess                     # COPY from public_html.htaccess
    └── uploads/                      # Property images
        └── properties/
            ├── prop-001/
            │   ├── image1.jpg
            │   └── image1_thumb.jpg
            └── prop-002/
```

---

## 📝 Step-by-Step Deployment

### STEP 1: Database Setup (10 minutes)

```bash
# 1. SSH into server
ssh yogahonc@your-server

# 2. Login to MySQL
mysql -u root -p

# 3. Create database (if not exists)
CREATE DATABASE IF NOT EXISTS yogahonc_consultingg788
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

# 4. Create user (if not exists)
CREATE USER IF NOT EXISTS 'yogahonc_consultingg788'@'localhost'
  IDENTIFIED BY 'PoloSport88*';

# 5. Grant privileges
GRANT ALL PRIVILEGES ON yogahonc_consultingg788.*
  TO 'yogahonc_consultingg788'@'localhost';

FLUSH PRIVILEGES;

# 6. Verify
SHOW DATABASES LIKE 'yogahonc_consultingg788';

# 7. Exit
EXIT;

# 8. Test connection
mysql -u yogahonc_consultingg788 -p'PoloSport88*' yogahonc_consultingg788

# If successful, you should see: "Welcome to the MySQL monitor"
# Type: EXIT;
```

**✅ Expected Result:** Database connection successful

---

### STEP 2: Backend Deployment (20 minutes)

```bash
# 1. Create backend directory structure
mkdir -p /home/yogahonc/goro.consultingg.com/backend
mkdir -p /home/yogahonc/goro.consultingg.com/backend/logs

# 2. Upload backend files
# FROM YOUR LOCAL MACHINE:
cd /path/to/project
tar -czf backend-deploy.tar.gz backend/

# Upload to server
scp backend-deploy.tar.gz yogahonc@server:/home/yogahonc/

# 3. Extract on server
ssh yogahonc@server
cd /home/yogahonc/goro.consultingg.com/
tar -xzf ~/backend-deploy.tar.gz

# 4. Copy production environment file
cd /home/yogahonc/goro.consultingg.com/backend
cp .env.production .env

# IMPORTANT: Edit .env and generate new JWT_SECRET
nano .env
# Replace JWT_SECRET with output from:
# openssl rand -hex 32

# 5. Install dependencies
cd /home/yogahonc/goro.consultingg.com/backend
npm install --production

# Also install dev dependencies for build
npm install --save-dev typescript @types/node ts-node prisma

# 6. Generate Prisma client
npx prisma generate

# 7. Build TypeScript
npm run build

# 8. Verify build
ls -la dist/
# Should see: server.js and other compiled files

# 9. Test database connection
node -e "
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();
prisma.\$connect()
  .then(() => console.log('✅ Database connected'))
  .then(() => prisma.property.count())
  .then(count => console.log('✅ Properties:', count))
  .catch(err => console.error('❌ Error:', err))
  .finally(() => prisma.\$disconnect());
"

# Expected output:
# ✅ Database connected
# ✅ Properties: [number]
```

**✅ Expected Result:** Build successful, database connection working

---

### STEP 3: Configure Node.js in cPanel (5 minutes)

1. **Login to cPanel** → https://your-cpanel-url
2. **Navigate to:** "Setup Node.js App"
3. **Click:** "Create Application"
4. **Configure:**
   - **Node.js version:** 18.20.8
   - **Application mode:** Production
   - **Application root:** goro.consultingg.com/backend
   - **Application URL:** goro.consultingg.com
   - **Application startup file:** dist/server.js
   - **Passenger log file:** logs/passenger.log

5. **Environment Variables:**
   ```
   NODE_ENV=production
   PORT=3000
   DATABASE_URL=mysql://yogahonc_consultingg788:PoloSport88*@localhost:3306/yogahonc_consultingg788
   PUBLIC_BASE_URL=https://goro.consultingg.com
   UPLOADS_DIR=/home/yogahonc/goro.consultingg.com/public_html/uploads
   JWT_SECRET=[your-generated-secret]
   CORS_ORIGIN=https://goro.consultingg.com,https://consultingg.com
   ```

6. **Click:** "Create" or "Save"
7. **Click:** "Run NPM Install" (if available)
8. **Click:** "Restart" to start the application

**✅ Expected Result:** Application status shows "Running"

---

### STEP 4: Frontend Deployment (10 minutes)

```bash
# FROM YOUR LOCAL MACHINE:

# 1. Build frontend for production
cd /path/to/project
npm run build

# Verify build output
ls -la dist/
# Should see: index.html, assets/, logo.png, robots.txt, etc.

# 2. Create deployment archive
tar -czf frontend-dist.tar.gz dist/

# 3. Upload to server
scp frontend-dist.tar.gz yogahonc@server:/home/yogahonc/

# 4. Extract on server
ssh yogahonc@server
cd /home/yogahonc/goro.consultingg.com/
tar -xzf ~/frontend-dist.tar.gz

# 5. Move contents to public_html
mkdir -p public_html
rm -rf public_html/*
mv dist/* public_html/

# 6. Copy .htaccess file
cp /path/to/public_html.htaccess public_html/.htaccess

# 7. Create uploads directory
mkdir -p public_html/uploads/properties

# 8. Set permissions
chmod -R 755 public_html/
chmod -R 755 public_html/uploads/

# 9. Verify structure
ls -la public_html/
# Should see: index.html, assets/, .htaccess, uploads/
```

**✅ Expected Result:** Frontend files deployed to public_html/

---

### STEP 5: Configure .htaccess (5 minutes)

```bash
# Verify .htaccess is in place
cat /home/yogahonc/goro.consultingg.com/public_html/.htaccess

# Should contain:
# - RewriteEngine On
# - ProxyPass /api http://localhost:3000/api
# - SPA fallback routing

# Test .htaccess syntax (if available)
# httpd -t
```

**Key .htaccess Rules:**
1. **API Proxy:** `/api/*` → `http://localhost:3000/api/*`
2. **Static Files:** Serve directly from disk
3. **SPA Routing:** Fallback to `index.html`
4. **Uploads:** Serve from `/uploads/` directory
5. **Caching:** Long cache for static assets
6. **Security:** Block sensitive files

**✅ Expected Result:** .htaccess file configured correctly

---

### STEP 6: Verify Deployment (15 minutes)

```bash
# 1. Test backend health endpoint
curl -I https://goro.consultingg.com/api/health
# Expected: HTTP/2 200

curl https://goro.consultingg.com/api/health
# Expected: {"success":true,"status":"healthy","database":"connected"}

# 2. Test properties API
curl https://goro.consultingg.com/api/properties | head -20
# Expected: JSON with properties array

# 3. Test root API endpoint
curl https://goro.consultingg.com/api/
# Expected: {"success":true,"name":"ConsultingG Real Estate API"}

# 4. Check backend logs (in cPanel)
# Navigate to Node.js App → View Logs
# Should see: "✅ Server started successfully"

# 5. Test image serving (if images exist)
curl -I https://goro.consultingg.com/uploads/properties/prop-001/test.jpg
# Expected: HTTP/2 200 or 404 (if file doesn't exist)
```

**✅ Expected Results:**
- `/api/health` returns 200 OK
- `/api/properties` returns property list
- No 500 errors in logs
- Backend shows "Running" status

---

### STEP 7: Test in Browser (10 minutes)

**Open:** https://goro.consultingg.com

**Test Checklist:**

1. **Homepage**
   - [ ] Page loads without errors
   - [ ] Properties display correctly
   - [ ] Images load (or show placeholder)
   - [ ] No console errors (press F12)

2. **Property Detail**
   - [ ] Click on a property
   - [ ] Detail page loads
   - [ ] Image gallery works
   - [ ] Description renders

3. **Search/Filter**
   - [ ] Search form works
   - [ ] Filters apply correctly
   - [ ] Results update

4. **Admin Panel**
   - [ ] Navigate to /admin/login
   - [ ] Login form displays
   - [ ] Enter credentials:
     - Email: georgiev@consultingg.com
     - Password: [your-admin-password]
   - [ ] Login succeeds
   - [ ] Dashboard loads
   - [ ] Can view properties
   - [ ] Can upload images (test with small image)

5. **Network Tab (F12 → Network)**
   - [ ] API calls return 200 OK
   - [ ] No 500 errors
   - [ ] No CORS errors
   - [ ] Images load from /uploads/

**✅ Expected Result:** All tests pass, no errors

---

## 🔧 Troubleshooting

### Issue: Backend won't start

```bash
# Check logs in cPanel Node.js App section
# Or SSH and check manually:

cd /home/yogahonc/goro.consultingg.com/backend
cat logs/passenger.log

# Common fixes:
# 1. Verify .env file exists
ls -la .env

# 2. Test database connection
mysql -u yogahonc_consultingg788 -p'PoloSport88*' yogahonc_consultingg788

# 3. Verify Prisma client generated
ls -la node_modules/.prisma/client/

# 4. Rebuild if needed
npm run build

# 5. Restart in cPanel
# Node.js App → Restart
```

---

### Issue: API returns 500 errors

```bash
# 1. Check backend logs
cat /home/yogahonc/goro.consultingg.com/backend/logs/passenger.log | tail -50

# 2. Test database connection
cd /home/yogahonc/goro.consultingg.com/backend
node -e "const {PrismaClient}=require('@prisma/client');new PrismaClient().\$connect().then(()=>console.log('OK')).catch(console.error);"

# 3. Verify DATABASE_URL in .env
cat .env | grep DATABASE_URL

# 4. Check Prisma schema matches database
npx prisma db pull
npx prisma generate

# 5. Restart application
# In cPanel: Node.js App → Restart
```

---

### Issue: Images don't load

```bash
# 1. Verify uploads directory exists
ls -la /home/yogahonc/goro.consultingg.com/public_html/uploads/

# 2. Check permissions
chmod -R 755 /home/yogahonc/goro.consultingg.com/public_html/uploads/

# 3. Test image URL directly
curl -I https://goro.consultingg.com/uploads/properties/test.jpg

# 4. Verify .htaccess doesn't block uploads
cat /home/yogahonc/goro.consultingg.com/public_html/.htaccess | grep -A 5 "uploads"

# 5. Check UPLOADS_DIR in backend .env
cat /home/yogahonc/goro.consultingg.com/backend/.env | grep UPLOADS_DIR
# Should be: /home/yogahonc/goro.consultingg.com/public_html/uploads
```

---

### Issue: CORS errors

```bash
# 1. Check CORS_ORIGIN in backend .env
cat /home/yogahonc/goro.consultingg.com/backend/.env | grep CORS_ORIGIN

# Should include: https://goro.consultingg.com

# 2. Restart backend after .env changes
# In cPanel: Node.js App → Restart

# 3. Check browser console for CORS error details
# F12 → Console
# Look for: "Access-Control-Allow-Origin"

# 4. Verify backend CORS configuration
# Check backend/src/server.ts has correct origins
```

---

### Issue: Admin login fails

```bash
# 1. Verify user exists in database
mysql -u yogahonc_consultingg788 -p'PoloSport88*' yogahonc_consultingg788
SELECT id, email, role FROM users WHERE email = 'georgiev@consultingg.com';
EXIT;

# 2. If user doesn't exist, create it:
# (You'll need to hash password with bcrypt first)

# 3. Check JWT_SECRET in .env
cat /home/yogahonc/goro.consultingg.com/backend/.env | grep JWT_SECRET

# 4. Check backend logs for auth errors
cat /home/yogahonc/goro.consultingg.com/backend/logs/passenger.log | grep -i "auth\|login"

# 5. Test login endpoint directly
curl -X POST https://goro.consultingg.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"georgiev@consultingg.com","password":"your-password"}'
```

---

## 📊 Monitoring & Maintenance

### Daily Checks

```bash
# 1. Check application status
# cPanel → Node.js App → Status should be "Running"

# 2. Check disk space
du -sh /home/yogahonc/goro.consultingg.com/*
df -h /home/yogahonc/

# 3. Review logs for errors
tail -100 /home/yogahonc/goro.consultingg.com/backend/logs/passenger.log

# 4. Test critical endpoints
curl -I https://goro.consultingg.com/api/health
curl -I https://goro.consultingg.com/api/properties
```

---

### Weekly Tasks

```bash
# 1. Database backup
mysqldump -u yogahonc_consultingg788 -p'PoloSport88*' \
  yogahonc_consultingg788 > ~/backups/db_$(date +%Y%m%d).sql

# 2. Check uploads folder size
du -sh /home/yogahonc/goro.consultingg.com/public_html/uploads/

# 3. Review application logs
# Check for repeated errors or warnings

# 4. Test admin panel functionality
# Login and verify CRUD operations work
```

---

## ✅ Success Criteria

**All Green:**
- ✅ Backend running on port 3000
- ✅ Database connected (yogahonc_consultingg788)
- ✅ Frontend loads at https://goro.consultingg.com
- ✅ API calls return valid data
- ✅ Images load from /uploads/
- ✅ Admin login works
- ✅ Property CRUD operations work
- ✅ Image upload works
- ✅ No 500 errors
- ✅ No CORS errors
- ✅ No console errors

---

## 📞 Quick Reference

### Important Paths

```
Backend App: /home/yogahonc/goro.consultingg.com/backend/
Frontend:    /home/yogahonc/goro.consultingg.com/public_html/
Uploads:     /home/yogahonc/goro.consultingg.com/public_html/uploads/
Logs:        /home/yogahonc/goro.consultingg.com/backend/logs/
```

### Database Credentials

```
Host:     localhost
Port:     3306
Database: yogahonc_consultingg788
User:     yogahonc_consultingg788
Password: PoloSport88*
```

### Key URLs

```
Homepage:    https://goro.consultingg.com
API Health:  https://goro.consultingg.com/api/health
API Root:    https://goro.consultingg.com/api/
Admin Login: https://goro.consultingg.com/admin/login
```

### Restart Backend

```bash
# Method 1: cPanel
# Navigate to: Node.js App → Restart button

# Method 2: SSH (if using PM2)
pm2 restart consultingg-backend

# Method 3: Touch restart file (Passenger)
touch /home/yogahonc/goro.consultingg.com/backend/tmp/restart.txt
```

---

## 🎉 Deployment Complete!

Your ConsultingG Real Estate application is now live and running correctly on production!

**Next Steps:**
1. Update DNS if needed (point domain to server)
2. Set up SSL certificate (Let's Encrypt via cPanel)
3. Configure daily database backups
4. Set up monitoring alerts
5. Test thoroughly before announcing launch

---

**Documentation Version:** 2.0 (Fixed)
**Last Updated:** 2025-10-27
**Status:** ✅ Production Ready
