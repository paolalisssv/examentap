import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { AuthService } from '../services/auth.service';

export const landingGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.tienePermiso('usuarios', 'consultar')) {
    return router.createUrlTree(['/usuarios']);
  }

  if (authService.tienePermiso('perfiles', 'consultar')) {
    return router.createUrlTree(['/perfiles']);
  }

  return router.createUrlTree(['/productos']);
};
