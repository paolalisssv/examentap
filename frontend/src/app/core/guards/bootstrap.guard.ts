import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

import { SystemService } from '../services/system.service';

export const bootstrapGuard: CanActivateFn = () => {
  const systemService = inject(SystemService);
  const router = inject(Router);

  if (systemService.initialized() === false) {
    return true;
  }

  return router.createUrlTree(['/login']);
};
