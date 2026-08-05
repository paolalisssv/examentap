export const JSON_CONTENT_TYPE = 'application/json';

export const AUTH_ENDPOINTS = {
  login: 'auth/login',
  logout: 'auth/logout',
  me: 'auth/me',
  forgotPassword: 'auth/forgot-password'
} as const;

export const AUTH_TOKEN_STORAGE_KEY = 'examentap_auth_token';
export const AUTH_USER_STORAGE_KEY = 'examentap_auth_user';
export const AUTH_PERMISOS_STORAGE_KEY = 'examentap_auth_permisos';

export const USUARIO_ENDPOINTS = {
  base: 'usuarios',
  exportPdf: 'usuarios/export/pdf',
  exportExcel: 'usuarios/export/excel'
} as const;

export const PERFIL_ENDPOINTS = {
  base: 'perfiles',
  exportPdf: 'perfiles/export/pdf',
  exportExcel: 'perfiles/export/excel'
} as const;

export const PRODUCTO_ENDPOINTS = {
  base: 'productos',
  exportPdf: 'productos/export/pdf',
  exportExcel: 'productos/export/excel'
} as const;

export const SYSTEM_ENDPOINTS = {
  status: 'system/status'
} as const;
