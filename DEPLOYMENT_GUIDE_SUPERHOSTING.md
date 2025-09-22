# 🚀 ConsultingG Real Estate - Deployment Guide за SuperHosting.bg

## 📋 Deployment Overview

**Target Environment:** SuperHosting.bg cPanel/Apache with PHP 8.2+  
**Document Root:** `/home/yogahon/consultingg.com`  
**Domain:** https://consultingg.com  
**Database:** Supabase PostgreSQL (Cloud)  

## 📁 Files to Upload

Upload all files from the `deploy-superhosting/` folder to your document root:

```
/home/yogahon/consultingg.com/
├── index.html              # React SPA entry point
├── assets/                 # CSS, JS, fonts (from Vite build)
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
│   ├── .env             # Environment variables
│   └── .htaccess        # API routing
├── uploads/              # User uploads (writable)
│   └── properties/       # Property images (writable)
├── .htaccess            # Main Apache config
└── .user.ini            # PHP settings
```

## 🚀 Deployment Steps

### **Step 1: Upload Files (5 minutes)**

1. **Login to cPanel** at your SuperHosting.bg account
2. **Open File Manager** → Navigate to `/home/yogahon/consultingg.com`
3. **Upload ZIP file** → Create ZIP from `deploy-superhosting/` folder
4. **Extract files** → Right-click ZIP → Extract → Extract to current directory
5. **Move contents** → Move all files from extracted folder to the root directory

### **Step 2: PHP Configuration (3 minutes)**

1. **cPanel → PHP Selector**
2. **Select PHP 8.2+**
3. **Enable Extensions:**
   - ✅ `pdo_pgsql` (PostgreSQL PDO driver)
   - ✅ `pgsql` (PostgreSQL functions)
   - ✅ `mbstring` (Multibyte string)
   - ✅ `fileinfo` (File information)
   - ✅ `json` (JSON functions)

### **Step 3: File Permissions (2 minutes)**

Set correct permissions via File Manager:

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
chmod 644 /home/yogahon/consultingg.com/api/.env
```

### **Step 4: Test Database Connection (2 minutes)**

1. **Via browser:** Visit `https://consultingg.com/api/database/install.php`
2. **Expected output:** Connection success with property/image counts

### **Step 5: SSL Setup (2 minutes)**

1. **cPanel → SSL/TLS**
2. **Let's Encrypt SSL** → Add for `consultingg.com`
3. **Force HTTPS Redirect** → Enable

## 🧪 Testing Checklist

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

### **Database Connection (Supabase PostgreSQL):**
- **Host:** `db.mveeovfztfczibtvkpas.supabase.co`
- **Port:** `5432`
- **Database:** `postgres`
- **User:** `postgres`
- **Password:** `PoloSport88*`
- **SSL:** Required

### **Admin Access:**
- **Email:** `georgiev@consultingg.com`
- **Password:** `PoloSport88*`
- **Role:** Administrator

### **File Upload:**
- **Max Size:** 20MB per file
- **Allowed Types:** JPEG, PNG, WebP
- **Max Images:** 50 per property
- **Storage:** `/home/yogahon/consultingg.com/uploads/properties/`

## 🚨 Troubleshooting

### **Common Issues:**

1. **500 Internal Server Error**
   - Check PHP error logs in cPanel
   - Verify file permissions (755 for directories, 644 for files)
   - Ensure PHP extensions are enabled

2. **Database Connection Failed**
   - Verify Supabase credentials in `api/.env`
   - Check if `pdo_pgsql` extension is enabled
   - Test connection: `php api/database/install.php`

3. **Images Not Uploading**
   - Check `/uploads/properties/` permissions (775)
   - Verify PHP upload settings in `.user.ini`
   - Check available disk space

4. **Admin Login Issues**
   - Verify JWT secret in `api/.env`
   - Check admin credentials in Supabase database
   - Clear browser cache and cookies

5. **SPA Routes Not Working**
   - Verify `.htaccess` rewrite rules
   - Check if mod_rewrite is enabled
   - Ensure `index.html` exists in document root

## ✅ Go-Live Checklist

### **Pre-Launch:**
- [ ] All files uploaded to hosting
- [x] Supabase PostgreSQL database ready (no import needed)
- [ ] Environment variables configured
- [ ] File permissions set correctly
- [ ] SSL certificate active
- [ ] Admin login works
- [ ] All pages load correctly
- [ ] API endpoints respond
- [ ] Image upload works
- [ ] Search functionality works
- [ ] Mobile responsiveness tested

### **Post-Launch:**
- [ ] Monitor error logs
- [ ] Test contact form submissions
- [ ] Verify SEO meta tags
- [ ] Check Google Analytics (if configured)
- [ ] Test performance with GTmetrix/PageSpeed
- [ ] Backup database and files

## 📞 Support

**SuperHosting.bg Support:** https://superhosting.bg/support  
**Supabase Dashboard:** https://supabase.com/dashboard  

---

**🎉 Deployment Ready!**  
Your ConsultingG Real Estate website is ready for deployment at https://consultingg.com with Supabase PostgreSQL backend!

## 📦 Next Steps

1. **Create ZIP file** from `deploy-superhosting/` folder
2. **Upload to SuperHosting.bg** via cPanel File Manager
3. **Extract files** to document root
4. **Set file permissions** as described above
5. **Test the website** using the checklist above

The project is now fully prepared for production deployment on SuperHosting.bg!