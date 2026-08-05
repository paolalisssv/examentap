export const SECCIONES_SISTEMA = ['usuarios', 'perfiles', 'productos'] as const;

export type SeccionSistema = (typeof SECCIONES_SISTEMA)[number];

export const SECCION_LABELS: Record<SeccionSistema, string> = {
  usuarios: 'Usuarios',
  perfiles: 'Perfiles',
  productos: 'Productos'
};
