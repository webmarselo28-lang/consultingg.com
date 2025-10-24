import { PrismaClient } from '@prisma/client';
import { logger } from '../utils/logger';

const prisma = new PrismaClient();

class CMSService {
  // =============================================================================
  // SECTIONS MANAGEMENT
  // =============================================================================

  async getSections(filters?: { page_slug?: string; type?: string; active?: boolean }) {
    try {
      const where: any = {};

      if (filters?.page_slug) {
        where.page_slug = filters.page_slug;
      }

      if (filters?.type) {
        where.type = filters.type;
      }

      if (filters?.active !== undefined) {
        where.active = filters.active;
      }

      const sections = await prisma.section.findMany({
        where,
        orderBy: { sort_order: 'asc' },
      });

      return { success: true, data: sections };
    } catch (error) {
      logger.error('Error fetching sections:', error);
      return { success: false, error: 'Failed to fetch sections' };
    }
  }

  async getSection(id: string) {
    try {
      const section = await prisma.section.findUnique({
        where: { id },
      });

      if (!section) {
        return { success: false, error: 'Section not found' };
      }

      return { success: true, data: section };
    } catch (error) {
      logger.error('Error fetching section:', error);
      return { success: false, error: 'Failed to fetch section' };
    }
  }

  async createSection(sectionData: any) {
    try {
      const section = await prisma.section.create({
        data: sectionData,
      });

      return { success: true, data: section };
    } catch (error) {
      logger.error('Error creating section:', error);
      return { success: false, error: 'Failed to create section' };
    }
  }

  async updateSection(id: string, updates: any) {
    try {
      const section = await prisma.section.update({
        where: { id },
        data: updates,
      });

      return { success: true, data: section };
    } catch (error) {
      logger.error('Error updating section:', error);
      return { success: false, error: 'Failed to update section' };
    }
  }

  async deleteSection(id: string) {
    try {
      await prisma.section.delete({
        where: { id },
      });

      return { success: true };
    } catch (error) {
      logger.error('Error deleting section:', error);
      return { success: false, error: 'Failed to delete section' };
    }
  }

  async reorderSections(updates: Array<{ id: string; sort_order: number }>) {
    try {
      const promises = updates.map(({ id, sort_order }) =>
        prisma.section.update({
          where: { id },
          data: { sort_order },
        })
      );

      await Promise.all(promises);
      return { success: true };
    } catch (error) {
      logger.error('Error reordering sections:', error);
      return { success: false, error: 'Failed to reorder sections' };
    }
  }

  // =============================================================================
  // PAGES MANAGEMENT
  // =============================================================================

  async getPages(activeOnly: boolean = true) {
    try {
      const where: any = {};

      if (activeOnly) {
        where.active = true;
      }

      const pages = await prisma.page.findMany({
        where,
        orderBy: { title: 'asc' },
      });

      return { success: true, data: pages };
    } catch (error) {
      logger.error('Error fetching pages:', error);
      return { success: false, error: 'Failed to fetch pages' };
    }
  }

  async getPageBySlug(slug: string) {
    try {
      const page = await prisma.page.findUnique({
        where: { slug },
      });

      if (!page) {
        return { success: false, error: 'Page not found' };
      }

      return { success: true, data: page };
    } catch (error) {
      logger.error('Error fetching page:', error);
      return { success: false, error: 'Failed to fetch page' };
    }
  }

  async createPage(pageData: any) {
    try {
      const page = await prisma.page.create({
        data: pageData,
      });

      return { success: true, data: page };
    } catch (error) {
      logger.error('Error creating page:', error);
      return { success: false, error: 'Failed to create page' };
    }
  }

  async updatePage(id: string, updates: any) {
    try {
      const page = await prisma.page.update({
        where: { id },
        data: updates,
      });

      return { success: true, data: page };
    } catch (error) {
      logger.error('Error updating page:', error);
      return { success: false, error: 'Failed to update page' };
    }
  }

  async deletePage(id: string) {
    try {
      await prisma.page.delete({
        where: { id },
      });

      return { success: true };
    } catch (error) {
      logger.error('Error deleting page:', error);
      return { success: false, error: 'Failed to delete page' };
    }
  }

  // =============================================================================
  // SERVICES MANAGEMENT
  // =============================================================================

  async getServices(activeOnly: boolean = true) {
    try {
      const where: any = {};

      if (activeOnly) {
        where.active = true;
      }

      const services = await prisma.service.findMany({
        where,
        orderBy: { sort_order: 'asc' },
      });

      return { success: true, data: services };
    } catch (error) {
      logger.error('Error fetching services:', error);
      return { success: false, error: 'Failed to fetch services' };
    }
  }

  async getService(id: string) {
    try {
      const service = await prisma.service.findUnique({
        where: { id },
      });

      if (!service) {
        return { success: false, error: 'Service not found' };
      }

      return { success: true, data: service };
    } catch (error) {
      logger.error('Error fetching service:', error);
      return { success: false, error: 'Failed to fetch service' };
    }
  }

  async createService(serviceData: any) {
    try {
      const service = await prisma.service.create({
        data: serviceData,
      });

      return { success: true, data: service };
    } catch (error) {
      logger.error('Error creating service:', error);
      return { success: false, error: 'Failed to create service' };
    }
  }

  async updateService(id: string, updates: any) {
    try {
      const service = await prisma.service.update({
        where: { id },
        data: updates,
      });

      return { success: true, data: service };
    } catch (error) {
      logger.error('Error updating service:', error);
      return { success: false, error: 'Failed to update service' };
    }
  }

  async deleteService(id: string) {
    try {
      await prisma.service.delete({
        where: { id },
      });

      return { success: true };
    } catch (error) {
      logger.error('Error deleting service:', error);
      return { success: false, error: 'Failed to delete service' };
    }
  }

  async reorderServices(updates: Array<{ id: string; sort_order: number }>) {
    try {
      const promises = updates.map(({ id, sort_order }) =>
        prisma.service.update({
          where: { id },
          data: { sort_order },
        })
      );

      await Promise.all(promises);
      return { success: true };
    } catch (error) {
      logger.error('Error reordering services:', error);
      return { success: false, error: 'Failed to reorder services' };
    }
  }
}

export const cmsService = new CMSService();
