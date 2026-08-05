import { SeccionPermiso } from '../../perfiles/models/seccion-permiso.model';
import { User } from './user.model';

export interface AuthResponse {
  user: User;
  token: string;
  tokenType: string;
  expiresAt: string;
  permisos: SeccionPermiso[];
}

export interface AuthResponseDto {
  user: User;
  token: string;
  token_type: string;
  expires_at: string;
  permisos: SeccionPermiso[];
}
