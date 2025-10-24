export interface JWTPayload {
  userId: string;
  email: string;
  role: string;
  aud: string;
}

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  total?: number;
  error?: string;
  message?: string;
}

export interface PropertyFilters {
  transaction_type?: string;
  property_type?: string;
  city_region?: string;
  min_price?: number;
  max_price?: number;
  min_area?: number;
  max_area?: number;
  bedrooms?: number;
  keyword?: string;
  featured?: boolean;
  active?: boolean;
  page?: number;
  limit?: number;
  sort?: string;
  order?: 'asc' | 'desc';
}
