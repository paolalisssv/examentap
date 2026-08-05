import { Routes } from '@angular/router';

import { permissionGuard } from '../../core/guards/permission.guard';

export const PERFILES_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./perfiles-list/perfiles-list.component').then((m) => m.PerfilesListComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'perfiles', permiso: 'consultar' }
  },
  {
    path: 'nuevo',
    loadComponent: () =>
      import('./perfil-form/perfil-form.component').then((m) => m.PerfilFormComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'perfiles', permiso: 'crear' }
  },
  {
    path: ':id/editar',
    loadComponent: () =>
      import('./perfil-form/perfil-form.component').then((m) => m.PerfilFormComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'perfiles', permiso: 'editar' }
  }
];
