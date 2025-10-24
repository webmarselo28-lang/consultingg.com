import { Router } from 'express';
import { propertyController } from '../controllers/PropertyController';
import { authenticate, requireAdmin } from '../middleware/auth';
import { propertyCreateValidation, propertyQueryValidation } from '../middleware/validator';

const router = Router();

router.get('/', propertyQueryValidation, propertyController.getAll.bind(propertyController));
router.get('/:id', propertyController.getById.bind(propertyController));
router.post('/', authenticate, requireAdmin, propertyCreateValidation, propertyController.create.bind(propertyController));
router.put('/:id', authenticate, requireAdmin, propertyController.update.bind(propertyController));
router.delete('/:id', authenticate, requireAdmin, propertyController.delete.bind(propertyController));

export default router;
