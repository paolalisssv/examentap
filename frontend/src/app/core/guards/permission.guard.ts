import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { SeccionPermiso } from '../../features/perfiles/models/seccion-permiso.model';
import { AuthService } from '../services/auth.service';

export const permissionGuard: CanActivateFn = (route) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  const seccion = route.data['seccion'] as string;
  const permiso = route.data['permiso'] as keyof Omit<SeccionPermiso, 'seccion'>;

  if (authService.tienePermiso(seccion, permiso)) {
    return true;
  }

  return router.createUrlTree(['/sin-acceso'], {
    queryParams: { error: 'No tienes permisos para acceder a este módulo.' }
  });
};
