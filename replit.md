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
- **Primary Database**: PostgreSQL (Neon Cloud for both development and production)
- **Previous Databases**: Originally MySQL, migrated to Replit Postgres, now on Neon Cloud
- **Migration Path**: MySQL → PostgreSQL (Replit) → Neon Cloud PostgreSQL
- **Schema**: Normalized database with properties, property_images, users, pages, sections, and services tables
- **Features**: UUID primary keys, JSONB for flexible data, optimized indexes
- **Neon Configuration**: ep-noisy-pine-agnly9s.eu-central-1.aws.neon.tech (EU-Central-1)

#### Database Configuration Notes
- **Development Environment**: Neon PostgreSQL with discrete DB_* environment variables
- **Production Environment**: Same Neon database (no separate production instance needed)
- **Replit Environment**: Database exports created in `/exports` directory for backup
- **SNI Support**: Automatic Neon endpoint injection in `backend/config/database.php`
- **Connection Method**: Direct host preferred (non-pooler) for maximum compatibility
- **SSL Mode**: Always required (`sslmode=require`)
- **Endpoint Auto-Detection**: Code automatically detects `.neon.tech` hosts and injects proper SNI parameters
- **Fallback Options**: DB_OPTIONS environment variable for manual endpoint override if needed

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
- **Neon PostgreSQL (Primary)**: Cloud-hosted serverless PostgreSQL database
  - **Host**: ep-noisy-pine-agnly9s.eu-central-1.aws.neon.tech (Direct, non-pooler)
  - **Region**: EU-Central-1 (AWS Frankfurt)
  - **Database**: neondb
  - **SNI Support**: Auto-detected and configured in backend/config/database.php
  - **Connection Test**: Available at `/backend/db_neon_check.php`
  - **Backup**: Database exports stored in `/exports` directory
- **Supabase (Frontend Only)**: Used for frontend Supabase client features if needed
  - **Connection**: Frontend configured with VITE_SUPABASE_URL and VITE_SUPABASE_ANON_KEY

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
- **Production Database**: Neon PostgreSQL (ep-noisy-pine-agnly9s.eu-central-1.aws.neon.tech)
- **Database SNI**: Automatic endpoint injection for Neon compatibility
- **PHP Extensions Required**: pdo_pgsql, pgsql, mbstring, json, fileinfo (enable in cPanel)
- **Environment Config**: Production template in `backend/.env.example.production`
- **Health Checks**: `/backend/db_test.php`, `/backend/db_neon_check.php`, `/api/`
- **Deployment Guide**: See `DEPLOY.md` for complete deployment instructions