import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { AuthService } from '../services/auth.service';
import { SystemService } from '../services/system.service';

export const guestGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const systemService = inject(SystemService);
  const router = inject(Router);

  // Mientras el sistema no tenga ningún usuario, toda ruta de invitado redirige
  // a la configuración inicial en vez de mostrar el login.
  if (systemService.initialized() === false) {
    return router.createUrlTree(['/configuracion-inicial']);
  }

  if (!authService.isAuthenticated()) {
    return true;
  }

  return router.createUrlTree(['/']);
};
