export type RolSlug = 'admin' | 'abogado' | 'pasante';

export interface Rol {
  id: number;
  slug: RolSlug;
  nombre: string;
  descripcion: string | null;
}

export interface User {
  id: number;
  nombres: string;
  apellidos: string;
  nombre_completo: string;
  numero_documento: string | null;
  email: string;
  telefono: string | null;
  rol_id: number;
  rol: RolSlug;
  rol_nombre: string;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

export interface UserPayload {
  nombres: string;
  apellidos: string;
  numero_documento?: string | null;
  email: string;
  telefono?: string | null;
  rol_id: number | null;
  activo: boolean;
  password?: string;
  password_confirmation?: string;
}

export interface Paginated<T> {
  data: T[];
  meta: { current_page: number; per_page: number; total: number; last_page: number };
}

export interface LoginResponse {
  token: string;
  user: User;
}
