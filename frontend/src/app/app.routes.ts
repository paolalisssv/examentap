import { Routes } from '@angular/router';

import { authGuard } from './core/guards/auth.guard';
import { bootstrapGuard } from './core/guards/bootstrap.guard';
import { guestGuard } from './core/guards/guest.guard';
import { landingGuard } from './core/guards/landing.guard';
import { MainLayoutComponent } from './core/layouts/main-layout/main-layout.component';
import { NotFoundComponent } from './shared/components/not-found/not-found.component';
import { SinAccesoComponent } from './shared/components/sin-acceso/sin-acceso.component';

export const routes: Routes = [
  {
    path: 'login',
    loadComponent: () =>
      import('./features/auth/login/login.component').then((m) => m.LoginComponent),
    canActivate: [guestGuard]
  },
  {
    path: 'configuracion-inicial',
    loadComponent: () =>
      import('./features/usuarios/usuario-form/usuario-form.component').then(
        (m) => m.UsuarioFormComponent
      ),
    canActivate: [bootstrapGuard],
    data: { bootstrap: true }
  },
  {
    path: 'forgot-password',
    loadComponent: () =>
      import('./features/auth/forgot-password/forgot-password.component').then(
        (m) => m.ForgotPasswordComponent
      ),
    canActivate: [guestGuard]
  },
  {
    path: '',
    component: MainLayoutComponent,
    canActivate: [authGuard],
    children: [
      {
        path: 'usuarios',
        loadChildren: () =>
          import('./features/usuarios/usuarios.routes').then((m) => m.USUARIOS_ROUTES)
      },
      {
        path: 'perfiles',
        loadChildren: () =>
          import('./features/perfiles/perfiles.routes').then((m) => m.PERFILES_ROUTES)
      },
      {
        path: 'productos',
        loadChildren: () =>
          import('./features/productos/productos.routes').then((m) => m.PRODUCTOS_ROUTES)
      },
      { path: 'sin-acceso', component: SinAccesoComponent },
      { path: '', pathMatch: 'full', canActivate: [landingGuard], children: [] },
      { path: '**', component: NotFoundComponent }
    ]
  }
];
