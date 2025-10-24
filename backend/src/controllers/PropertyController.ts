import { Request, Response, NextFunction } from 'express';
import { validationResult } from 'express-validator';
import { propertyService } from '../services/propertyService';
import { PropertyFilters } from '../types';

export class PropertyController {
  async getAll(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        res.status(400).json({ success: false, error: 'Validation failed', errors: errors.array() });
        return;
      }

      const filters: PropertyFilters = {
        transaction_type: req.query.transaction_type as string,
        property_type: req.query.property_type as string,
        city_region: req.query.city_region as string,
        min_price: req.query.min_price ? parseFloat(req.query.min_price as string) : undefined,
        max_price: req.query.max_price ? parseFloat(req.query.max_price as string) : undefined,
        min_area: req.query.min_area ? parseFloat(req.query.min_area as string) : undefined,
        max_area: req.query.max_area ? parseFloat(req.query.max_area as string) : undefined,
        bedrooms: req.query.bedrooms ? parseInt(req.query.bedrooms as string, 10) : undefined,
        keyword: req.query.keyword as string,
        featured: req.query.featured === 'true' ? true : req.query.featured === 'false' ? false : undefined,
        active: req.query.active === 'false' ? false : true,
        page: req.query.page ? parseInt(req.query.page as string, 10) : 1,
        limit: req.query.limit ? parseInt(req.query.limit as string, 10) : 20,
        sort: (req.query.sort as string) || 'sort_order',
        order: (req.query.order as 'asc' | 'desc') || 'asc',
      };

      const result = await propertyService.getAll(filters);
      res.json({ success: true, data: result.data, total: result.total });
    } catch (error) {
      next(error);
    }
  }

  async getById(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const { id } = req.params;
      if (!id) {
        res.status(400).json({ success: false, error: 'Property ID required' });
        return;
      }
      const property = await propertyService.getById(id);
      res.json({ success: true, data: property });
    } catch (error) {
      next(error);
    }
  }

  async create(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        res.status(400).json({ success: false, error: 'Validation failed', errors: errors.array() });
        return;
      }
      const property = await propertyService.create(req.body);
      res.status(201).json({ success: true, data: property, message: 'Property created' });
    } catch (error) {
      next(error);
    }
  }

  async update(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const { id } = req.params;
      if (!id) {
        res.status(400).json({ success: false, error: 'Property ID required' });
        return;
      }
      const property = await propertyService.update(id, req.body);
      res.json({ success: true, data: property, message: 'Property updated' });
    } catch (error) {
      next(error);
    }
  }

  async delete(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const { id } = req.params;
      if (!id) {
        res.status(400).json({ success: false, error: 'Property ID required' });
        return;
      }
      const result = await propertyService.delete(id);
      res.json({ success: true, message: result.message });
    } catch (error) {
      next(error);
    }
  }
}

export const propertyController = new PropertyController();
