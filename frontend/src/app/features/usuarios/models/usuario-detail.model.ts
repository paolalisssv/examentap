import { Perfil, PerfilDto, mapPerfil } from '../../perfiles/models/perfil.model';
import { SeccionPermiso } from '../../perfiles/models/seccion-permiso.model';
import { Usuario, UsuarioDto, mapUsuario } from './usuario.model';

export interface UsuarioDetail {
  usuario: Usuario;
  perfiles: Perfil[];
  secciones: SeccionPermiso[];
}

export interface UsuarioDetailDto {
  usuario: UsuarioDto;
  perfiles: PerfilDto[];
  secciones: SeccionPermiso[];
}

export function mapUsuarioDetail(dto: UsuarioDetailDto): UsuarioDetail {
  return {
    usuario: mapUsuario(dto.usuario),
    perfiles: dto.perfiles.map(mapPerfil),
    secciones: dto.secciones
  };
}
