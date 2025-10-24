import prisma from '../config/database';
import { PropertyFilters } from '../types';
import { AppError } from '../middleware/errorHandler';
import { generateUUID } from '../utils/uuid';

export class PropertyService {
  async getAll(filters: PropertyFilters) {
    const { transaction_type, property_type, city_region, min_price, max_price, min_area, max_area, bedrooms, keyword, featured, active = true, page = 1, limit = 20, sort = 'sort_order', order = 'asc' } = filters;

    const where: any = { active };
    if (transaction_type) where.transaction_type = transaction_type;
    if (property_type) where.property_type = property_type;
    if (city_region) where.city_region = city_region;
    if (min_price || max_price) where.price = { ...(min_price && { gte: min_price }), ...(max_price && { lte: max_price }) };
    if (min_area || max_area) where.area = { ...(min_area && { gte: min_area }), ...(max_area && { lte: max_area }) };
    if (bedrooms) where.bedrooms = bedrooms;
    if (featured !== undefined) where.featured = featured;

    if (keyword) {
      const words = keyword.split(/\s+/).filter(w => w.length > 0);
      where.AND = words.map(word => ({
        OR: [
          { title: { contains: word } },
          { description: { contains: word } },
          { city_region: { contains: word } },
          { district: { contains: word } },
          { address: { contains: word } },
          { property_code: { contains: word } },
          { property_type: { contains: word } }
        ]
      }));
    }

    const skip = (page - 1) * limit;
    const [properties, total] = await Promise.all([
      prisma.properties.findMany({ where, include: { property_images: { orderBy: { sort_order: 'asc' } } }, orderBy: { [sort]: order }, skip, take: limit }),
      prisma.properties.count({ where })
    ]);

    const PUBLIC_BASE_URL = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';
    const enriched = properties.map((p: any) => ({
      ...p,
      images: (p.property_images || []).map((img: any) => ({
        ...img,
        url: `${PUBLIC_BASE_URL}${img.image_url}?v=${Date.now()}`,
        thumbnail_url: `${PUBLIC_BASE_URL}${img.image_url.replace(/(\.[^.]+)$/, '_thumb$1')}?v=${Date.now()}`
      }))
    }));

    return { data: enriched, total, page, limit, totalPages: Math.ceil(total / limit) };
  }

  async getById(id: string) {
    const property = await prisma.properties.findFirst({
      where: { OR: [{ id }, { property_code: id }] },
      include: { property_images: { orderBy: { sort_order: 'asc' } } }
    });
    if (!property) throw new AppError('Property not found', 404);

    const PUBLIC_BASE_URL = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';
    return {
      ...property,
      images: (property.property_images || []).map((img: any) => ({
        ...img,
        url: `${PUBLIC_BASE_URL}${img.image_url}?v=${Date.now()}`,
        thumbnail_url: `${PUBLIC_BASE_URL}${img.image_url.replace(/(\.[^.]+)$/, '_thumb$1')}?v=${Date.now()}`
      }))
    };
  }

  async create(data: any) {
    const id = generateUUID();
    const property_code = data.property_code || await this.generateNextPropertyCode();
    const maxSort = await prisma.properties.findFirst({ orderBy: { sort_order: 'desc' }, select: { sort_order: true } });
    const sort_order = (maxSort?.sort_order || 0) + 1;

    return await prisma.properties.create({
      data: { id, ...data, property_code, sort_order, active: data.active !== undefined ? data.active : true },
      include: { property_images: true }
    });
  }

  async update(id: string, data: any) {
    const existing = await prisma.properties.findFirst({ where: { OR: [{ id }, { property_code: id }] } });
    if (!existing) throw new AppError('Property not found', 404);
    return await prisma.properties.update({ where: { id: existing.id }, data, include: { property_images: true } });
  }

  async delete(id: string) {
    const property = await prisma.properties.findFirst({ where: { OR: [{ id }, { property_code: id }] } });
    if (!property) throw new AppError('Property not found', 404);
    await prisma.property_images.deleteMany({ where: { property_id: property.id } });
    await prisma.properties.delete({ where: { id: property.id } });
    return { success: true, message: 'Property deleted' };
  }

  private async generateNextPropertyCode(): Promise<string> {
    const last = await prisma.properties.findFirst({ where: { property_code: { startsWith: 'prop-' } }, orderBy: { property_code: 'desc' } });
    if (!last?.property_code) return 'prop-001';
    const match = last.property_code.match(/prop-(\d+)/);
    return match ? `prop-${String(parseInt(match[1], 10) + 1).padStart(3, '0')}` : 'prop-001';
  }
}

export const propertyService = new PropertyService();
