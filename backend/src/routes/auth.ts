import { Router } from 'express';
import { authController } from '../controllers/AuthController';
import { authenticate } from '../middleware/auth';
import { loginValidation } from '../middleware/validator';

const router = Router();

router.post('/login', loginValidation, authController.login.bind(authController));
router.get('/me', authenticate, authController.getCurrentUser.bind(authController));
router.post('/logout', authenticate, authController.logout.bind(authController));

export default router;
