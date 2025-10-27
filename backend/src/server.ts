import express, { Request, Response, NextFunction } from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import dotenv from 'dotenv';
import path from 'path';

import { connectDatabase, disconnectDatabase } from './config/database';
import { logger } from './utils/logger';
import { errorHandler, notFoundHandler } from './middleware/errorHandler';
import routes from './routes';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;
const IS_PRODUCTION = process.env.NODE_ENV === 'production';

// Security middleware
app.use(helmet({
  crossOriginResourcePolicy: { policy: 'cross-origin' },
  contentSecurityPolicy: IS_PRODUCTION ? undefined : false,
}));

// Compression middleware
app.use(compression());

// CORS configuration
const corsOrigins = process.env.CORS_ORIGIN?.split(',') || [
  'http://localhost:5173',
  'http://localhost:5000',
  'http://localhost:3000',
  'https://consultingg.com',
  'https://www.consultingg.com',
  'https://goro.consultingg.com'
];

app.use(cors({
  origin: corsOrigins,
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With'],
  exposedHeaders: ['X-Total-Count', 'X-Page-Count'],
}));

// Trust proxy for behind nginx/apache
if (IS_PRODUCTION) {
  app.set('trust proxy', 1);
}

// Request parsing middleware
app.use(express.json({ limit: process.env.MAX_FILE_SIZE || '20mb' }));
app.use(express.urlencoded({ extended: true, limit: process.env.MAX_FILE_SIZE || '20mb' }));

// Request logging middleware
app.use((req: Request, res: Response, next: NextFunction) => {
  const start = Date.now();
  res.on('finish', () => {
    const duration = Date.now() - start;
    logger.info({
      method: req.method,
      path: req.path,
      status: res.statusCode,
      duration: `${duration}ms`,
      ip: req.ip || req.socket.remoteAddress,
    });
  });
  next();
});

// Serve static files from uploads directory with proper CORS and caching
// PRODUCTION: Use absolute path from environment variable or default to relative
const uploadsPath = process.env.UPLOADS_DIR || process.env.UPLOAD_DIR || path.join(__dirname, '..', '..', 'uploads');
logger.info(`📁 Serving static files from: ${uploadsPath}`);

// Ensure uploads directory exists
const fs = require('fs');
if (!fs.existsSync(uploadsPath)) {
  logger.warn(`⚠️ Uploads directory does not exist: ${uploadsPath}`);
  logger.info(`Creating uploads directory...`);
  fs.mkdirSync(uploadsPath, { recursive: true });
}

app.use('/uploads', express.static(uploadsPath, {
  maxAge: '1d',
  setHeaders: (res, filePath) => {
    res.setHeader('Cache-Control', 'public, max-age=86400');
    res.setHeader('Access-Control-Allow-Origin', '*');
  }
}));

// Root endpoint
app.get('/', (_req: Request, res: Response) => {
  res.json({
    success: true,
    ok: true,
    name: 'ConsultingG Real Estate API',
    version: '1.0.0',
    environment: process.env.NODE_ENV || 'development',
    endpoints: {
      auth: '/api/auth',
      properties: '/api/properties',
      images: '/api/images',
      cms: '/api/cms',
      health: '/health',
    },
  });
});

// Health check endpoint
app.get('/health', async (_req: Request, res: Response) => {
  try {
    const { PrismaClient } = await import('@prisma/client');
    const prisma = new PrismaClient();
    await prisma.$queryRaw`SELECT 1`;
    await prisma.$disconnect();

    res.json({
      success: true,
      status: 'healthy',
      timestamp: new Date().toISOString(),
      uptime: process.uptime(),
      memory: {
        used: Math.round(process.memoryUsage().heapUsed / 1024 / 1024) + 'MB',
        total: Math.round(process.memoryUsage().heapTotal / 1024 / 1024) + 'MB',
      },
      database: 'connected',
      uploadsPath: uploadsPath,
      publicBaseUrl: process.env.PUBLIC_BASE_URL || 'http://localhost:3000',
    });
  } catch (error) {
    res.status(503).json({
      success: false,
      status: 'unhealthy',
      database: 'disconnected',
      error: 'Database connection failed',
    });
  }
});

app.use('/api', routes);
app.use(notFoundHandler);
app.use(errorHandler);

// Global error handlers
process.on('uncaughtException', (error: Error) => {
  logger.error('Uncaught Exception:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason: any) => {
  logger.error('Unhandled Rejection:', reason);
  process.exit(1);
});

const startServer = async () => {
  try {
    logger.info('🔄 Starting ConsultingG Backend Server...');

    // Connect to database
    await connectDatabase();

    // Start listening
    const server = app.listen(PORT, () => {
      logger.info('✅ Server started successfully');
      logger.info(`🚀 Backend API running on port ${PORT}`);
      logger.info(`📍 Environment: ${process.env.NODE_ENV || 'development'}`);
      logger.info(`🌐 Public URL: ${process.env.PUBLIC_BASE_URL || 'http://localhost:3000'}`);
      logger.info(`📁 Uploads directory: ${uploadsPath}`);
      logger.info(`🔒 CORS Origins: ${corsOrigins.join(', ')}`);
    });

    // Graceful shutdown handlers
    const gracefulShutdown = async (signal: string) => {
      logger.info(`${signal} received, starting graceful shutdown...`);

      server.close(async () => {
        logger.info('HTTP server closed');
        await disconnectDatabase();
        logger.info('Graceful shutdown completed');
        process.exit(0);
      });

      // Force shutdown after 30 seconds
      setTimeout(() => {
        logger.error('Forced shutdown after timeout');
        process.exit(1);
      }, 30000);
    };

    process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
    process.on('SIGINT', () => gracefulShutdown('SIGINT'));

  } catch (error) {
    logger.error('Failed to start server:', error);
    process.exit(1);
  }
};

startServer();

export default app;
