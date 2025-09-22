# 🚀 ConsultingG Real Estate - Production Deployment Guide

## 📋 Deployment Overview

**Target Environment:** SuperHosting.bg cPanel/Apache with PHP 8.2  
**Document Root:** `/home/yogahon/consultingg.com`  
**Domain:** https://consultingg.com  
**Database:** Supabase PostgreSQL  

## 📁 Package Contents

```
deploy-package/
├── index.html              # Frontend entry point
├── assets/                 # CSS, JS, fonts (from Vite build)
├── images/                 # Static property images
├── api/                    # PHP backend
│   ├── index.php          # API router
│   ├── controllers/       # API controllers
│   ├── models/           # Database models
│   ├── middleware/       # Auth middleware
│   ├── routes/           # API routes
│   ├── utils/            # Utilities
│   ├── config/           # Configuration
│   └── .htaccess         # API routing rules
├── uploads/              # Writable upload directory
│   └── properties/       # Property images (writable)
├── .htaccess            # Main Apache config
├── .user.ini            # PHP settings
├── .env.production.example # Environment template
└── DEPLOY_README.md     # This file
```

## 🚀 Deployment Steps

### **Step 1: Upload Files (5 minutes)**

1. **Login to cPanel** at your SuperHosting.bg account
2. **Open File Manager** → Navigate to `/home/yogahon/consultingg.com`
3. **Upload ZIP file** → Select `deploy-package.zip`
4. **Extract files** → Right-click ZIP → Extract → Extract to current directory
5. **Move contents** → Move all files from `deploy-package/` to the root directory
6. **Delete ZIP** → Remove `deploy-package.zip` and empty `deploy-package/` folder

### **Step 2: Environment Configuration (3 minutes)**

1. **Copy environment file:**
   ```bash
   cp .env.production.example .env
   ```

2. **Edit .env file** and update:
   ```env
   DB_PASS=<YOUR_SUPABASE_DB_PASSWORD>
   JWT_SECRET=<GENERATE_RANDOM_SECRET_KEY>
   ```

3. **Generate JWT Secret** (use online generator or):
   ```bash
   openssl rand -base64 32
   ```

### **Step 3: PHP Configuration (2 minutes)**

1. **cPanel → PHP Selector**
2. **Select PHP 8.2**
3. **Enable Extensions:**
   - ✅ `pdo_pgsql` (PostgreSQL PDO driver)
   - ✅ `pgsql` (PostgreSQL functions)
   - ✅ `mbstring` (Multibyte string)
   - ✅ `fileinfo` (File information)
   - ✅ `json` (JSON functions)

### **Step 4: File Permissions (2 minutes)**

Set correct permissions via File Manager or SSH:

```bash
# Main directories
chmod 755 /home/yogahon/consultingg.com
chmod 755 /home/yogahon/consultingg.com/api
chmod 755 /home/yogahon/consultingg.com/uploads
chmod 775 /home/yogahon/consultingg.com/uploads/properties

# Configuration files
chmod 644 /home/yogahon/consultingg.com/.env
chmod 644 /home/yogahon/consultingg.com/.htaccess
chmod 644 /home/yogahon/consultingg.com/api/.htaccess
```

### **Step 5: SSL Setup (2 minutes)**

1. **cPanel → SSL/TLS**
2. **Let's Encrypt SSL** → Add for `consultingg.com`
3. **Force HTTPS Redirect** → Enable (or use .htaccess rules)

## 🧪 Health Checks

### **Frontend Tests:**
- [ ] **Homepage:** https://consultingg.com ✅
- [ ] **Properties:** https://consultingg.com/properties ✅
- [ ] **Property Detail:** Click on any property ✅
- [ ] **About Page:** https://consultingg.com/about ✅
- [ ] **Contact Page:** https://consultingg.com/contact ✅
- [ ] **Mobile Responsive:** Test on mobile device ✅

### **API Tests:**
```bash
# Test API health
curl https://consultingg.com/api/health

# Test properties endpoint
curl https://consultingg.com/api/properties

# Test services endpoint
curl https://consultingg.com/api/services
```

### **Admin Panel Tests:**
- [ ] **Login:** https://consultingg.com/admin/login ✅
- [ ] **Credentials:** `georgiev@consultingg.com` / `PoloSport88*` ✅
- [ ] **Dashboard:** View statistics ✅
- [ ] **Properties List:** View all properties ✅
- [ ] **Add Property:** Create new property ✅
- [ ] **Image Upload:** Test image upload functionality ✅
- [ ] **Edit Property:** Modify existing property ✅

## 🔧 Configuration Details

### **Database Connection:**
- **Host:** `db.mveeovfztfczibtvkpas.supabase.co`
- **Port:** `5432`
- **Database:** `postgres`
- **User:** `postgres`
- **SSL:** Required

### **File Upload:**
- **Max Size:** 20MB per file
- **Allowed Types:** JPEG, PNG, WebP
- **Max Images:** 50 per property
- **Storage:** `/home/yogahon/consultingg.com/uploads/properties/`

### **Admin Access:**
- **Email:** `georgiev@consultingg.com`
- **Password:** `PoloSport88*`
- **Role:** Administrator

## 🚨 Troubleshooting

### **Common Issues:**

1. **500 Internal Server Error**
   - Check PHP error logs in cPanel
   - Verify file permissions (755 for directories, 644 for files)
   - Ensure PHP extensions are enabled

2. **Database Connection Failed**
   - Verify Supabase credentials in `.env`
   - Check if `pdo_pgsql` extension is enabled
   - Test connection from phpMyAdmin or database tools

3. **Images Not Uploading**
   - Check `/uploads/properties/` permissions (775)
   - Verify PHP upload settings in `.user.ini`
   - Check available disk space

4. **Admin Login Issues**
   - Verify JWT secret in `.env`
   - Check admin credentials in database
   - Clear browser cache and cookies

5. **SPA Routes Not Working**
   - Verify `.htaccess` rewrite rules
   - Check if mod_rewrite is enabled
   - Ensure `index.html` exists in document root

### **Log Locations:**
- **PHP Errors:** cPanel → Error Logs
- **Apache Errors:** cPanel → Error Logs
- **Application Logs:** Check browser console for frontend errors

## 📊 Expected File Structure After Deployment

```
/home/yogahon/consultingg.com/
├── index.html              # React SPA entry point
├── assets/                 # Vite build assets
│   ├── index-[hash].js    # Main JS bundle
│   ├── index-[hash].css   # Main CSS bundle
│   └── vendor-[hash].js   # Vendor libraries
├── images/                 # Static property images
├── logo.png               # Site logo
├── robots.txt             # SEO robots file
├── sitemap.xml            # SEO sitemap
├── api/                   # PHP backend
│   ├── index.php         # API entry point
│   ├── controllers/      # Business logic
│   ├── models/           # Database models
│   ├── routes/           # API routes
│   ├── config/           # Configuration
│   ├── middleware/       # Authentication
│   ├── utils/            # Utilities
│   └── .htaccess        # API routing
├── uploads/              # User uploads (writable)
│   └── properties/       # Property images (writable)
├── .htaccess            # Main Apache config
├── .user.ini            # PHP settings
└── .env                 # Environment variables
```

## 🎯 Post-Deployment Verification

### **Immediate Checks:**
1. **Site loads:** https://consultingg.com
2. **API responds:** https://consultingg.com/api/health
3. **Admin login works:** https://consultingg.com/admin/login
4. **Properties display:** Frontend shows property listings
5. **Images load:** Property images display correctly

### **Functional Tests:**
1. **Search properties** with different filters
2. **View property details** with image galleries
3. **Admin CRUD operations** (create, edit, delete properties)
4. **Image management** (upload, delete, set main image)
5. **Mobile responsiveness** on different devices

## 📞 Support

**For deployment issues:**
- **SuperHosting.bg Support:** Contact hosting provider for server-related issues
- **Application Issues:** Check error logs and verify configuration

**Admin Access:**
- **URL:** https://consultingg.com/admin/login
- **Email:** georgiev@consultingg.com
- **Password:** PoloSport88*

---

## ✅ Deployment Complete!

After following these steps, your ConsultingG Real Estate platform will be live at:
**https://consultingg.com**

The site includes:
- ✅ Modern React frontend with property search
- ✅ PHP backend API with Supabase PostgreSQL
- ✅ Admin panel for content management
- ✅ Image upload system
- ✅ SEO optimization
- ✅ Mobile responsive design
- ✅ Security headers and SSL