import { Router } from 'express';
import { imageController } from '../controllers/ImageController';
import { authenticate, requireAdmin } from '../middleware/auth';
import { upload } from '../middleware/upload';

const router = Router();

router.post('/upload', authenticate, requireAdmin, upload.array('images', 50), imageController.upload.bind(imageController));
router.put('/:propertyId/images/:imageId/set-main', authenticate, requireAdmin, imageController.setMain.bind(imageController));
router.delete('/:id', authenticate, requireAdmin, imageController.delete.bind(imageController));

export default router;
