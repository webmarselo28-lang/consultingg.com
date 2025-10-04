# ConsultingG Real Estate

## Overview

ConsultingG Real Estate is a modern, full-stack real estate management platform built for the Bulgarian market. The application serves as a comprehensive property listing and management system with a React-based frontend and PHP backend API. The platform features property search, detailed listings, content management, and administrative tools for real estate professionals.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Framework**: React 18 with TypeScript for type safety
- **Build Tool**: Vite for fast development and optimized builds
- **Styling**: Tailwind CSS for utility-first styling
- **Routing**: React Router DOM for client-side navigation
- **State Management**: React hooks with custom hooks for business logic
- **SEO**: React Helmet Async for dynamic meta tags and structured data
- **Forms**: React Hook Form with Yup validation

### Backend Architecture
- **Language**: PHP 8.0+ with object-oriented design
- **API Design**: RESTful API with centralized routing
- **Environment Variables**: vlucas/phpdotenv for `.env` file loading
- **Authentication**: JWT-based authentication using Firebase JWT
- **File Handling**: Custom image upload and management system
- **Architecture Pattern**: MVC pattern with controllers, models, and services
- **Error Handling**: Centralized error handling with proper HTTP status codes
- **Security**: CORS configuration, input validation, and SQL injection protection

### Database Design
- **Primary Database**: PostgreSQL (Replit-hosted for development)
- **External Database**: Supabase PostgreSQL configured for production deployment
- **Migration Path**: Successfully migrated from MySQL to PostgreSQL
- **Schema**: Normalized database with properties, property_images, users, pages, sections, and services tables
- **Features**: UUID primary keys, JSONB for flexible data, Row Level Security (RLS)
- **Indexes**: Optimized indexes for search performance

#### Database Configuration Notes
- **Replit Environment**: Uses Replit's internal PostgreSQL database (DATABASE_URL) for development
- **Supabase Integration**: Frontend Supabase client configured with VITE_SUPABASE_URL and VITE_SUPABASE_ANON_KEY
- **Production**: External Supabase database available for deployment outside Replit environment
- **Network Limitation**: Replit's current environment cannot directly connect to external databases like Supabase
- **Data Migration**: Database dump created and imported to Supabase for future production use

### File Structure
- **Frontend**: Standard React/TypeScript structure in `/src`
- **Backend**: PHP API in `/backend` with clear separation of concerns
- **Public Assets**: Static images and files in `/public` and `/images`
- **Deployment**: Multiple deployment packages for different hosting environments

### Key Design Decisions
- **Database Migration**: Chose PostgreSQL over MySQL for better JSON support, UUID handling, and advanced features
- **Image Management**: Local file storage with organized directory structure for better performance
- **API Architecture**: Single entry point (`/api/index.php`) with route-based dispatch for clean URLs
- **Environment Flexibility**: Multiple configuration setups for development, production, and various hosting providers

## External Dependencies

### Database Services
- **Supabase PostgreSQL**: Cloud-hosted PostgreSQL database with built-in auth and real-time features
- **Connection Details**: Uses environment variables for secure configuration
- **Row Level Security**: Configured for public read access and admin write access

### PHP Dependencies (Composer)
- **vlucas/phpdotenv**: Environment variable loading from `.env` files
- **firebase/php-jwt**: JWT token generation and validation for authentication
- **ext-pdo**: PostgreSQL database connectivity
- **ext-pgsql**: PostgreSQL extension
- **ext-json**: JSON data handling
- **ext-mbstring**: String manipulation for Bulgarian language support
- **ext-fileinfo**: File type detection for uploads

### Frontend Dependencies (npm)
- **@supabase/supabase-js**: Direct database client for additional features
- **lucide-react**: Modern icon library
- **react-router-dom**: Client-side routing
- **react-hook-form**: Form handling and validation
- **react-helmet-async**: SEO meta tag management
- **yup**: Schema validation
- **qrcode.react**: QR code generation for contact information

### Development Tools
- **TypeScript**: Static type checking
- **ESLint**: Code linting with React-specific rules
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast build tool and development server
- **PostCSS/Autoprefixer**: CSS post-processing

### Hosting Integration
- **SuperHosting.bg**: Primary deployment target (consultingg.com)
- **Apache/PHP Configuration**: .htaccess rules for clean URLs, API routing, and MIME types
- **SSL/HTTPS**: Enforced secure connections with CSP headers
- **File Permissions**: Proper upload directory permissions for image management
- **Production Database**: Supabase PostgreSQL (db.gtvcakkgqlpfdivpejmi.supabase.co)
- **Environment Config**: Production credentials in `backend/.env` file
- **Deployment Guide**: See `DEPLOY.md` for complete deployment instructions