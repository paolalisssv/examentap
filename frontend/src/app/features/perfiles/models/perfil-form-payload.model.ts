import { SeccionPermiso } from './seccion-permiso.model';

export interface PerfilFormPayload {
  name: string;
  secciones: SeccionPermiso[];
}
