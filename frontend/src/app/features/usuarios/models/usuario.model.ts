export interface Usuario {
  id: string;
  name: string;
  email: string;
  telefono: string | null;
  fotoUrl: string;
  perfiles: string[];
  createdAt: string;
}

export interface UsuarioDto {
  id: string;
  name: string;
  email: string;
  telefono: string | null;
  foto_url: string;
  perfiles: string[];
  created_at: string;
}

export function mapUsuario(dto: UsuarioDto): Usuario {
  return {
    id: dto.id,
    name: dto.name,
    email: dto.email,
    telefono: dto.telefono,
    fotoUrl: dto.foto_url,
    perfiles: dto.perfiles,
    createdAt: dto.created_at
  };
}
