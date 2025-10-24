import prisma from '../config/database';
import { imageHelper } from '../utils/imageHelper';
import { generateUUID } from '../utils/uuid';
import { AppError } from '../middleware/errorHandler';

export class ImageService {
  async uploadImages(files: Express.Multer.File[], propertyId: string, isMain: boolean = false) {
    const property = await prisma.properties.findUnique({ where: { id: propertyId } });
    if (!property) throw new AppError('Property not found', 404);

    const maxSort = await prisma.property_images.findFirst({ where: { property_id: propertyId }, orderBy: { sort_order: 'desc' }, select: { sort_order: true } });
    let sortOrder = (maxSort?.sort_order || 0) + 1;

    if (isMain) {
      await prisma.property_images.updateMany({ where: { property_id: propertyId }, data: { is_main: false } });
    }

    const uploadedImages = [];
    for (const file of files) {
      const validation = imageHelper.validateImageFile(file);
      if (!validation.valid) throw new AppError(validation.error || 'Invalid file', 400);

      const { imagePath } = await imageHelper.processImage(file, propertyId);
      const image: any = await prisma.property_images.create({
        data: { id: generateUUID(), property_id: propertyId, image_url: imagePath, image_path: imagePath, sort_order: sortOrder++, is_main: isMain && uploadedImages.length === 0 }
      });

      const PUBLIC_BASE_URL = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';
      uploadedImages.push({
        ...image,
        url: `${PUBLIC_BASE_URL}${image.image_url}?v=${Date.now()}`,
        thumbnail_url: `${PUBLIC_BASE_URL}${image.image_url.replace(/(\.[^.]+)$/, '_thumb$1')}?v=${Date.now()}`
      });
    }
    return uploadedImages;
  }

  async deleteImage(imageId: string) {
    const image = await prisma.property_images.findUnique({ where: { id: imageId } });
    if (!image) throw new AppError('Image not found', 404);
    await imageHelper.deleteImage(image.image_url);
    await prisma.property_images.delete({ where: { id: imageId } });
    return { success: true, message: 'Image deleted' };
  }

  async setMainImage(propertyId: string, imageId: string) {
    const image = await prisma.property_images.findUnique({ where: { id: imageId } });
    if (!image || image.property_id !== propertyId) throw new AppError('Image not found', 404);
    await prisma.property_images.updateMany({ where: { property_id: propertyId }, data: { is_main: false } });
    await prisma.property_images.update({ where: { id: imageId }, data: { is_main: true } });
    return { success: true, message: 'Main image updated' };
  }
}

export const imageService = new ImageService();
