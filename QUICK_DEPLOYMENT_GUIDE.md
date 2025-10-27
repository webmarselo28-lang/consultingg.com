# ⚡ Quick Deployment Guide - ConsultingG Real Estate

**For:** Experienced developers who need a fast deployment reference
**Time:** 30 minutes
**Target:** https://goro.consultingg.com

---

## 📦 Files to Upload

**Generated Files (Ready to Deploy):**
```
backend/.env.production          → backend/.env (rename)
public_html.htaccess            → public_html/.htaccess (rename)
.env.production                 → (for reference only)
```

**Directories to Upload:**
```
backend/         → /home/yogahonc/goro.consultingg.com/backend/
dist/            → /home/yogahonc/goro.consultingg.com/public_html/
```

---

## 🚀 3-Step Deployment

### STEP 1: Backend (10 min)

```bash
# Upload & extract
scp backend-deploy.tar.gz yogahonc@server:/home/yogahonc/
ssh yogahonc@server
cd /home/yogahonc/goro.consultingg.com/
tar -xzf ~/backend-deploy.tar.gz

# Configure
cd backend
cp .env.production .env
nano .env  # Add JWT_SECRET: openssl rand -hex 32

# Build
npm install --production
npm install --save-dev typescript @types/node ts-node prisma
npx prisma generate
npm run build

# Test
node -e "require('@prisma/client').PrismaClient().then(p=>p.\$connect()).then(()=>console.log('OK'))"
```

**cPanel Setup:**
- Node.js App → Create Application
- App root: `goro.consultingg.com/backend`
- Startup: `dist/server.js`
- Node.js: `18.20.8`
- Mode: `Production`
- Add env vars from `.env.production`
- **Restart**

✅ Check: Status = "Running"

---

### STEP 2: Frontend (5 min)

```bash
# Upload & extract
scp frontend-dist.tar.gz yogahonc@server:/home/yogahonc/
ssh yogahonc@server
cd /home/yogahonc/goro.consultingg.com/
tar -xzf ~/frontend-dist.tar.gz

# Deploy
mkdir -p public_html
rm -rf public_html/*
mv dist/* public_html/
cp /path/to/public_html.htaccess public_html/.htaccess

# Uploads
mkdir -p public_html/uploads/properties
chmod -R 755 public_html/
```

✅ Check: Files in `public_html/`

---

### STEP 3: Verify (5 min)

```bash
# API test
curl https://goro.consultingg.com/api/health
# Expected: {"success":true,"status":"healthy"}

curl https://goro.consultingg.com/api/properties | head -20
# Expected: JSON array

# Browser test
# Open: https://goro.consultingg.com
# Check: No console errors, properties load
```

✅ Success: All green

---

## 🔑 Key Credentials

**Database:**
```
Host: localhost
DB:   yogahonc_consultingg788
User: yogahonc_consultingg788
Pass: PoloSport88*
```

**Environment Variables:**
```env
NODE_ENV=production
PORT=3000
DATABASE_URL="mysql://yogahonc_consultingg788:PoloSport88*@localhost:3306/yogahonc_consultingg788"
PUBLIC_BASE_URL=https://goro.consultingg.com
UPLOADS_DIR=/home/yogahonc/goro.consultingg.com/public_html/uploads
CORS_ORIGIN=https://goro.consultingg.com,https://consultingg.com
JWT_SECRET=[generate with: openssl rand -hex 32]
JWT_EXPIRES_IN=7d
JWT_AUD=consultingg.com
```

---

## 🩹 Quick Fixes

**Backend stopped?**
```bash
# cPanel → Node.js App → Restart
```

**500 errors?**
```bash
cd backend
npx prisma generate
npm run build
# cPanel → Restart
```

**Images not loading?**
```bash
chmod -R 755 public_html/uploads/
```

**CORS errors?**
```bash
# Check backend/.env has correct CORS_ORIGIN
# cPanel → Restart
```

---

## 📊 Health Checks

```bash
✅ https://goro.consultingg.com/api/health
✅ https://goro.consultingg.com/api/properties
✅ https://goro.consultingg.com (homepage)
✅ https://goro.consultingg.com/admin/login
```

---

## 🎯 Success Checklist

- [ ] Backend: Running
- [ ] Database: Connected
- [ ] API: 200 responses
- [ ] Frontend: Loads
- [ ] Images: Serve
- [ ] Admin: Accessible
- [ ] Console: No errors

**Done! 🎉**

---

**Quick Reference Version:** 1.0
**Deployment Time:** ~30 minutes
