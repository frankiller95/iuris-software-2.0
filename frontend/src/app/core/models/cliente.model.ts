export type TipoPersona = 'natural' | 'juridica';
export type Genero = 'masculino' | 'femenino';

export interface Municipio {
  id: number;
  codigo: string;
  nombre: string;
  departamento?: string;
  etiqueta: string; // "Cali (Valle del Cauca)"
}

export interface Cliente {
  id: number;
  tipo_persona: TipoPersona;
  numero_documento: string;
  nombre: string;
  genero: Genero | null;
  municipio_id: number | null;
  municipio: Municipio | null;
  telefono: string | null;
  email: string | null;
  direccion: string | null;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

export interface ClientePayload {
  tipo_persona: TipoPersona;
  numero_documento: string;
  nombre: string;
  genero: Genero | null;
  municipio_id: number | null;
  telefono: string | null;
  email: string | null;
  direccion: string | null;
  activo: boolean;
}
