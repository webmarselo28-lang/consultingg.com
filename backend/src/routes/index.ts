import { Router } from 'express';
import authRoutes from './auth';
import propertyRoutes from './properties';
import imageRoutes from './images';

const router = Router();

router.use('/auth', authRoutes);
router.use('/properties', propertyRoutes);
router.use('/images', imageRoutes);

export default router;
