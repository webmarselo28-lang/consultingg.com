import jwt, { SignOptions } from 'jsonwebtoken';
import { JWTPayload } from '../types';

const JWT_SECRET = process.env.JWT_SECRET || 'consultingg-jwt-secret-key-2024';
const JWT_AUD = process.env.JWT_AUD || 'consultingg.com';
const JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || '24h';

export const generateToken = (payload: Omit<JWTPayload, 'aud'>): string => {
  const signPayload: Record<string, any> = { ...payload, aud: JWT_AUD };
  return jwt.sign(signPayload, JWT_SECRET, { expiresIn: JWT_EXPIRES_IN } as SignOptions);
};

export const verifyToken = (token: string): JWTPayload => {
  return jwt.verify(token, JWT_SECRET, { audience: JWT_AUD }) as JWTPayload;
};
