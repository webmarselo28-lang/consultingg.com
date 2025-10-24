import path from 'path';
import fs from 'fs/promises';
import sharp from 'sharp';

export class ImageHelper {
  private uploadsDir: string;

  constructor() {
    this.uploadsDir = path.join(process.cwd(), '..', 'uploads', 'properties');
  }

  async ensurePropertyDirectory(propertyId: string): Promise<string> {
    const propertyDir = path.join(this.uploadsDir, propertyId);
    try {
      await fs.access(propertyDir);
    } catch {
      await fs.mkdir(propertyDir, { recursive: true });
    }
    return propertyDir;
  }

  async processImage(file: Express.Multer.File, propertyId: string): Promise<{ imagePath: string }> {
    const propertyDir = await this.ensurePropertyDirectory(propertyId);
    const timestamp = Date.now();
    const randomStr = Math.random().toString(36).substring(2, 8);
    const baseName = path.parse(file.originalname).name.toLowerCase().replace(/[^a-z0-9а-яё._-]/g, '_');
    const ext = '.jpg';

    const imageFileName = `prop_${propertyId}_${baseName}_${timestamp}_${randomStr}${ext}`;
    const thumbnailFileName = `prop_${propertyId}_${baseName}_${timestamp}_${randomStr}_thumb${ext}`;

    await sharp(file.buffer).resize(1920, 1080, { fit: 'inside', withoutEnlargement: true }).jpeg({ quality: 85 }).toFile(path.join(propertyDir, imageFileName));
    await sharp(file.buffer).resize(400, 300, { fit: 'cover' }).jpeg({ quality: 80 }).toFile(path.join(propertyDir, thumbnailFileName));

    return { imagePath: `/uploads/properties/${propertyId}/${imageFileName}` };
  }

  async deleteImage(imagePath: string): Promise<void> {
    try {
      const fullPath = path.join(process.cwd(), '..', imagePath);
      await fs.unlink(fullPath);
      const thumbnailPath = imagePath.replace(/(\.[^.]+)$/, '_thumb$1');
      try { await fs.unlink(path.join(process.cwd(), '..', thumbnailPath)); } catch {}
    } catch {}
  }

  validateImageFile(file: Express.Multer.File): { valid: boolean; error?: string } {
    const allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSize = parseInt(process.env.MAX_FILE_SIZE || '10485760', 10);
    if (!allowedMimeTypes.includes(file.mimetype)) return { valid: false, error: 'Invalid file type' };
    if (file.size > maxSize) return { valid: false, error: 'File too large' };
    return { valid: true };
  }
}

export const imageHelper = new ImageHelper();
