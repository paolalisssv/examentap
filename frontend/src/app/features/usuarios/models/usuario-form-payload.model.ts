export interface UsuarioFormPayload {
  name: string;
  email: string;
  password?: string;
  telefono?: string;
  foto?: File;
  perfiles?: string[];
}
