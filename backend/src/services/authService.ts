import bcrypt from 'bcrypt';
import prisma from '../config/database';
import { generateToken } from '../utils/jwt';
import { AppError } from '../middleware/errorHandler';

export class AuthService {
  async login(email: string, password: string) {
    const user = await prisma.user.findFirst({ where: { email, active: true } });
    if (!user || !(await bcrypt.compare(password, user.password))) {
      throw new AppError('Invalid credentials', 401);
    }
    const token = generateToken({ userId: user.id, email: user.email, role: user.role || 'user' });
    return { token, user: { id: user.id, email: user.email, name: user.name || '', role: user.role || 'user' } };
  }

  async getCurrentUser(userId: string) {
    const user = await prisma.user.findUnique({ where: { id: userId }, select: { id: true, email: true, name: true, role: true } });
    if (!user) throw new AppError('User not found', 404);
    return user;
  }
}

export const authService = new AuthService();
