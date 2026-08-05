import { Routes } from '@angular/router';

import { permissionGuard } from '../../core/guards/permission.guard';

export const USUARIOS_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./usuarios-list/usuarios-list.component').then((m) => m.UsuariosListComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'usuarios', permiso: 'consultar' }
  },
  {
    path: 'nuevo',
    loadComponent: () =>
      import('./usuario-form/usuario-form.component').then((m) => m.UsuarioFormComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'usuarios', permiso: 'crear' }
  },
  {
    path: ':id/editar',
    loadComponent: () =>
      import('./usuario-form/usuario-form.component').then((m) => m.UsuarioFormComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'usuarios', permiso: 'editar' }
  }
];
