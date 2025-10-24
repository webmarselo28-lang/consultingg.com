import { Request, Response, NextFunction } from 'express';
import { imageService } from '../services/imageService';

export class ImageController {
  async upload(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      if (!req.files || !Array.isArray(req.files) || req.files.length === 0) {
        res.status(400).json({ success: false, error: 'No files uploaded' });
        return;
      }
      const { property_id, is_main } = req.body;
      if (!property_id) {
        res.status(400).json({ success: false, error: 'Property ID required' });
        return;
      }
      const images = await imageService.uploadImages(req.files, property_id, is_main === 'true' || is_main === true);
      res.json({ success: true, data: images, message: 'Images uploaded' });
    } catch (error) {
      next(error);
    }
  }

  async delete(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const { id } = req.params;
      if (!id) {
        res.status(400).json({ success: false, error: 'Image ID required' });
        return;
      }
      const result = await imageService.deleteImage(id);
      res.json({ success: true, message: result.message });
    } catch (error) {
      next(error);
    }
  }

  async setMain(req: Request, res: Response, next: NextFunction): Promise<void> {
    try {
      const { propertyId, imageId } = req.params;
      if (!propertyId || !imageId) {
        res.status(400).json({ success: false, error: 'Property ID and Image ID required' });
        return;
      }
      const result = await imageService.setMainImage(propertyId, imageId);
      res.json({ success: true, message: result.message });
    } catch (error) {
      next(error);
    }
  }
}

export const imageController = new ImageController();
