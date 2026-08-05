import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { AuthService } from '../services/auth.service';
import { SystemService } from '../services/system.service';

export const authGuard: CanActivateFn = (_route, state) => {
  const authService = inject(AuthService);
  const systemService = inject(SystemService);
  const router = inject(Router);

  if (systemService.initialized() === false) {
    return router.createUrlTree(['/configuracion-inicial']);
  }

  if (authService.isAuthenticated()) {
    return true;
  }

  return router.createUrlTree(['/login'], { queryParams: { returnUrl: state.url } });
};
