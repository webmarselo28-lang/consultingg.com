import { body, query, ValidationChain } from 'express-validator';

export const loginValidation: ValidationChain[] = [
  body('email').isEmail().withMessage('Valid email is required'),
  body('password').isLength({ min: 6 }).withMessage('Password required'),
];

export const propertyCreateValidation: ValidationChain[] = [
  body('title').trim().notEmpty().isLength({ max: 255 }),
  body('price').isNumeric().isFloat({ min: 0 }),
  body('currency').isIn(['EUR', 'BGN', 'USD']),
  body('transaction_type').isIn(['sale', 'rent']),
  body('property_type').notEmpty(),
  body('city_region').notEmpty(),
  body('area').isNumeric().isFloat({ min: 0 }),
];

export const propertyQueryValidation: ValidationChain[] = [
  query('page').optional().isInt({ min: 1 }),
  query('limit').optional().isInt({ min: 1, max: 100 }),
];
