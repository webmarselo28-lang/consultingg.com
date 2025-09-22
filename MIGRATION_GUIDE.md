# 🚀 PostgreSQL Migration Guide: Supabase → SuperHosting.bg

## 📋 Migration Overview

**Source:** Supabase PostgreSQL (Cloud)  
**Target:** SuperHosting.bg PostgreSQL (Self-hosted)  
**Database:** `superhosting_postgresql`  
**User:** `superhosting_user`  

## 🛠️ Prerequisites

### **SuperHosting.bg Requirements:**
- PostgreSQL 12+ installed and running
- Access to PostgreSQL superuser account
- `pg_dump` and `psql` tools available
- Network access to Supabase (for export)

### **Local Requirements:**
- PostgreSQL client tools (`pg_dump`, `psql`)
- Access to Supabase database credentials
- Bash shell for running scripts

## 🚀 Migration Steps

### **Step 1: Create SuperHosting Database (5 minutes)**

1. **Login to SuperHosting.bg cPanel/SSH**
2. **Connect as PostgreSQL superuser:**
   ```bash
   sudo -u postgres psql
   ```

3. **Run database creation script:**
   ```bash
   psql -U postgres -f scripts/create_superhosting_database.sql
   ```

4. **Verify database creation:**
   ```sql
   \l
   \c superhosting_postgresql
   \du
   ```

### **Step 2: Export Supabase Data (10 minutes)**

1. **Set Supabase password environment variable:**
   ```bash
   export PGPASSWORD="PoloSport88*"
   ```

2. **Run export script:**
   ```bash
   chmod +x scripts/export_supabase_data.sh
   ./scripts/export_supabase_data.sh
   ```

3. **Verify export file:**
   ```bash
   ls -la supabase_backup.sql
   head -20 supabase_backup.sql
   ```

### **Step 3: Import to SuperHosting (10 minutes)**

1. **Set SuperHosting password environment variable:**
   ```bash
   export PGPASSWORD="SuperHosting2024!SecurePassword"
   ```

2. **Run import script:**
   ```bash
   chmod +x scripts/import_to_superhosting.sh
   ./scripts/import_to_superhosting.sh
   ```

3. **Verify import:**
   ```bash
   psql -h localhost -U superhosting_user -d superhosting_postgresql -f scripts/verify_migration.sql
   ```

### **Step 4: Update Application Configuration (5 minutes)**

1. **Copy environment template:**
   ```bash
   cp .env.superhosting .env.production
   ```

2. **Update backend database configuration:**
   - Environment variables are already configured in `backend/config/database.php`
   - No code changes needed - uses environment variables

3. **Test connection:**
   ```bash
   php backend/database/install.php
   ```

## 🧪 Testing the Migration

### **Database Verification:**
```sql
-- Connect to SuperHosting database
psql -h localhost -U superhosting_user -d superhosting_postgresql

-- Check table counts
SELECT 
    'properties' as table_name, COUNT(*) as records FROM properties
UNION ALL
SELECT 
    'services' as table_name, COUNT(*) as records FROM services
UNION ALL
SELECT 
    'pages' as table_name, COUNT(*) as records FROM pages;

-- Test UUID generation
SELECT uuid_generate_v4();

-- Check extensions
\dx
```

### **Application Testing:**
```bash
# Test API endpoints
curl http://localhost/api/health
curl http://localhost/api/properties
curl http://localhost/api/services
curl http://localhost/api/pages

# Test admin login
curl -X POST "http://localhost/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "georgiev@consultingg.com", "password": "PoloSport88*"}'
```

## 📊 Expected Migration Results

### **Tables to be migrated:**
- ✅ `properties` - Real estate listings
- ✅ `property_images` - Property photos
- ✅ `services` - CMS services
- ✅ `pages` - CMS pages
- ✅ `users` - Admin users

### **Data to be migrated:**
- ✅ **5 Properties** with all characteristics
- ✅ **15 Property Images** with local paths
- ✅ **6 Services** for homepage display
- ✅ **3 Pages** (About, Contact, Services)
- ✅ **1 Admin User** with authentication

### **Extensions to be enabled:**
- ✅ `uuid-ossp` - UUID generation
- ✅ `pg_trgm` - Text search (if available)
- ✅ `unaccent` - Text normalization (if available)

## 🔧 Configuration Details

### **Database Connection:**
- **Host:** `localhost` (SuperHosting.bg)
- **Port:** `5432`
- **Database:** `superhosting_postgresql`
- **User:** `superhosting_user`
- **Password:** `SuperHosting2024!SecurePassword`
- **SSL Mode:** `prefer` (adjust based on SuperHosting config)

### **Environment Variables:**
```bash
DB_HOST=localhost
DB_PORT=5432
DB_NAME=superhosting_postgresql
DB_USER=superhosting_user
DB_PASS=SuperHosting2024!SecurePassword
DATABASE_URL=postgres://superhosting_user:SuperHosting2024!SecurePassword@localhost:5432/superhosting_postgresql
```

## 🚨 Troubleshooting

### **Common Issues:**

1. **Connection Failed:**
   - Verify PostgreSQL is running on SuperHosting
   - Check firewall settings
   - Verify user credentials

2. **Permission Denied:**
   - Ensure `superhosting_user` has proper privileges
   - Check database ownership
   - Verify schema permissions

3. **Extension Errors:**
   - Some extensions may not be available on shared hosting
   - Skip optional extensions if they fail
   - Core functionality works without `pg_trgm` and `unaccent`

4. **Import Errors:**
   - Check for conflicting table names
   - Verify PostgreSQL version compatibility
   - Review error logs for specific issues

### **Rollback Plan:**
If migration fails, simply update `.env.production` back to Supabase credentials:
```bash
cp .env.supabase .env.production
```

## ✅ Post-Migration Checklist

- [ ] Database `superhosting_postgresql` created successfully
- [ ] User `superhosting_user` has proper privileges
- [ ] All tables imported with correct schema
- [ ] All data migrated successfully
- [ ] Extensions enabled (uuid-ossp required)
- [ ] Application connects to new database
- [ ] API endpoints respond correctly
- [ ] Admin panel functions properly
- [ ] Services and Pages CMS modules work
- [ ] Property listings display correctly

## 📞 Support

**SuperHosting.bg Support:** Contact hosting provider for PostgreSQL-specific issues  
**Migration Issues:** Check error logs and verify connection parameters  
**Application Issues:** Test API endpoints and database connectivity  

---

## 🎉 Migration Complete!

After successful migration, your ConsultingG Real Estate application will be running on SuperHosting.bg PostgreSQL with full functionality preserved.

**New Database URL:** `postgres://superhosting_user:SuperHosting2024!SecurePassword@localhost:5432/superhosting_postgresql`