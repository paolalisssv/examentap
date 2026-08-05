import { Routes } from '@angular/router';

import { permissionGuard } from '../../core/guards/permission.guard';

export const PRODUCTOS_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./productos-list/productos-list.component').then((m) => m.ProductosListComponent)
  },
  {
    path: 'nuevo',
    loadComponent: () =>
      import('./producto-form/producto-form.component').then((m) => m.ProductoFormComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'productos', permiso: 'crear' }
  },
  {
    path: ':id/editar',
    loadComponent: () =>
      import('./producto-form/producto-form.component').then((m) => m.ProductoFormComponent),
    canActivate: [permissionGuard],
    data: { seccion: 'productos', permiso: 'editar' }
  }
];
