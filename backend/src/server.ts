import express, { Request, Response } from 'express';
import cors from 'cors';
import helmet from 'helmet';
import dotenv from 'dotenv';
import path from 'path';

import { connectDatabase, disconnectDatabase } from './config/database';
import { logger } from './utils/logger';
import { errorHandler, notFoundHandler } from './middleware/errorHandler';
import routes from './routes';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

app.use(helmet({ crossOriginResourcePolicy: { policy: 'cross-origin' } }));
app.use(cors({
  origin: ['http://localhost:5173', 'http://localhost:5000', 'http://localhost:3000', 'https://consultingg.com', 'https://www.consultingg.com'],
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'Accept'],
}));

app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Serve static files from uploads directory with proper CORS and caching
const uploadsPath = path.join(__dirname, '..', '..', 'uploads');
logger.info(`📁 Serving static files from: ${uploadsPath}`);
app.use('/uploads', express.static(uploadsPath, {
  maxAge: '1d',
  setHeaders: (res, filePath) => {
    res.setHeader('Cache-Control', 'public, max-age=86400');
    res.setHeader('Access-Control-Allow-Origin', '*');
  }
}));

app.get('/', (_req: Request, res: Response) => {
  res.json({
    success: true,
    ok: true,
    message: 'ConsultingG Real Estate API - Node.js Backend',
    version: '1.0.0',
    endpoints: {
      auth: '/api/auth/login',
      properties: '/api/properties',
      images: '/api/images',
    },
  });
});

app.get('/health', (_req: Request, res: Response) => {
  res.json({
    success: true,
    message: 'Backend running',
    uploadsPath: uploadsPath,
    publicBaseUrl: process.env.PUBLIC_BASE_URL || 'http://localhost:3000',
  });
});

app.use('/api', routes);
app.use(notFoundHandler);
app.use(errorHandler);

const startServer = async () => {
  try {
    await connectDatabase();
    app.listen(PORT, () => {
      logger.info(`🚀 Backend API running on port ${PORT}`);
      logger.info(`📍 Environment: ${process.env.NODE_ENV || 'development'}`);
    });
  } catch (error) {
    logger.error('Failed to start server:', error);
    process.exit(1);
  }
};

process.on('SIGTERM', async () => {
  logger.info('SIGTERM received');
  await disconnectDatabase();
  process.exit(0);
});

process.on('SIGINT', async () => {
  logger.info('SIGINT received');
  await disconnectDatabase();
  process.exit(0);
});

startServer();

export default app;
