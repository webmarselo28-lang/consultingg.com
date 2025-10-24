# ConsultingG Real Estate - Node.js Backend

🚀 **Node.js/Express/TypeScript/Prisma Backend API**

## ✅ Migration Complete

All PHP files have been **completely removed** and replaced with modern Node.js stack.

## 📦 Technology Stack

- **Runtime**: Node.js 22.x
- **Framework**: Express.js 4.19
- **ORM**: Prisma 5.22 (MySQL 8.0)
- **Language**: TypeScript 5.7
- **Authentication**: JWT
- **Image Processing**: Sharp
- **Security**: Helmet, CORS
- **Logging**: Winston

## 🏗️ Project Structure

```
backend/
├── src/
│   ├── config/
│   │   └── database.ts              # Prisma connection
│   ├── controllers/
│   │   ├── AuthController.ts        # /api/auth/*
│   │   ├── PropertyController.ts    # /api/properties/*
│   │   └── ImageController.ts       # /api/images/*
│   ├── routes/
│   │   ├── index.ts                 # Main router
│   │   ├── auth.ts
│   │   ├── properties.ts
│   │   └── images.ts
│   ├── middleware/
│   │   ├── auth.ts                  # JWT authentication
│   │   ├── errorHandler.ts
│   │   ├── upload.ts                # Multer config
│   │   └── validator.ts
│   ├── services/
│   │   ├── authService.ts
│   │   ├── propertyService.ts
│   │   └── imageService.ts
│   ├── utils/
│   │   ├── jwt.ts
│   │   ├── uuid.ts
│   │   ├── logger.ts
│   │   └── imageHelper.ts
│   ├── types/
│   │   └── index.ts
│   └── server.ts                    # Entry point
├── prisma/
│   └── schema.prisma                # Database schema
├── .env
├── package.json
└── tsconfig.json
```

## 🔧 Setup

### 1. Install Dependencies

```bash
cd backend
npm install
```

### 2. Generate Prisma Schema

```bash
npm run prisma:pull        # Pull schema from MySQL
npm run prisma:generate    # Generate Prisma Client
```

### 3. Configure Environment

Edit `.env`:

```env
NODE_ENV=production
PORT=3000
DATABASE_URL="mysql://yogahonc_consultingg78:PoloSport88*@localhost:3306/yogahonc_consultingg78"
PUBLIC_BASE_URL=https://consultingg.com
JWT_SECRET=consultingg-jwt-secret-key-2024
```

### 4. Build & Run

```bash
npm run build    # Compile TypeScript → dist/
npm start        # Run production server
```

**Development mode:**
```bash
npm run dev      # Hot reload with ts-node-dev
```

## 🌐 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `GET /api/auth/me` - Get current user (authenticated)
- `POST /api/auth/logout` - Logout

### Properties
- `GET /api/properties` - List properties (with search/filters)
- `GET /api/properties/:id` - Get property by ID
- `POST /api/properties` - Create property (admin)
- `PUT /api/properties/:id` - Update property (admin)
- `DELETE /api/properties/:id` - Delete property (admin)

### Images
- `POST /api/images/upload` - Upload images (admin)
- `PUT /api/images/:propertyId/images/:imageId/set-main` - Set main image (admin)
- `DELETE /api/images/:id` - Delete image (admin)

## 🔍 Search Functionality

Multi-word keyword search with AND logic:

```
keyword: "Боян къща"
```

Searches in:
- `title`
- `description`
- `city_region`
- `district`
- `address`
- `property_code`
- `property_type`

**Both words must match** (AND logic).

## 📸 Image Processing

- **Original**: Max 1920x1080px, 85% quality
- **Thumbnail**: 400x300px, 80% quality
- **Format**: JPEG
- **Naming**: `prop_{id}_{name}_{timestamp}_{random}.jpg`
- **Thumbnail suffix**: `_thumb.jpg`

## 🔐 Security

- JWT authentication for admin endpoints
- Helmet security headers
- CORS configured for consultingg.com
- Input validation with express-validator
- Parameterized queries (Prisma)
- File type & size validation

## 📊 Response Format

**Success:**
```json
{
  "success": true,
  "data": { ... },
  "total": 100
}
```

**Error:**
```json
{
  "success": false,
  "error": "Error message"
}
```

## 🚀 Production Deployment

### With PM2

```bash
npm install -g pm2
cd backend
npm run build
pm2 start dist/server.js --name consultingg-api
pm2 save
pm2 startup
```

### Nginx Configuration

```nginx
location /api/ {
    proxy_pass http://localhost:3000/api/;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

## 📝 Scripts

```bash
npm run dev              # Development with hot reload
npm run build            # Build TypeScript
npm start                # Run production server
npm run prisma:pull      # Pull DB schema
npm run prisma:generate  # Generate Prisma Client
npm test                 # Run tests
```

## ✅ Migration Status

- ✅ All 34 PHP files deleted
- ✅ Node.js/Express/TypeScript backend created
- ✅ 21 TypeScript source files
- ✅ Complete API implementation
- ✅ Authentication with JWT
- ✅ Property CRUD with search
- ✅ Image upload with Sharp processing
- ✅ Prisma ORM with MySQL 8.0
- ✅ Security middleware
- ✅ Error handling
- ✅ Production-ready configuration

## 📞 Support

For issues or questions, check logs:

```bash
pm2 logs consultingg-api
```

---

**Status**: ✅ Production Ready
**Port**: 3000
**Database**: MySQL 8.0
**Version**: 1.0.0
