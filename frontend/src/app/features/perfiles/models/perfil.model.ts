import { SeccionPermiso } from './seccion-permiso.model';

export interface Perfil {
  id: string;
  name: string;
  secciones: SeccionPermiso[];
  createdAt: string;
}

export interface PerfilDto {
  id: string;
  name: string;
  secciones: SeccionPermiso[];
  created_at: string;
}

export function mapPerfil(dto: PerfilDto): Perfil {
  return {
    id: dto.id,
    name: dto.name,
    secciones: dto.secciones,
    createdAt: dto.created_at
  };
}
