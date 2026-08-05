import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

import { AUTH_ENDPOINTS } from '../constants/api.constants';
import { AuthService } from '../services/auth.service';

const PUBLIC_AUTH_PATHS: string[] = [AUTH_ENDPOINTS.login, AUTH_ENDPOINTS.forgotPassword];

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  const isPublicAuthRequest = PUBLIC_AUTH_PATHS.some((path) => req.url.includes(path));
  const token = authService.getToken();

  const authorizedRequest =
    token && !isPublicAuthRequest
      ? req.clone({ setHeaders: { Authorization: `Bearer ${token}` } })
      : req;

  return next(authorizedRequest).pipe(
    catchError((error: HttpErrorResponse) => {
      // En login/forgot-password un 401 son credenciales inválidas, no una sesión
      // expirada, así que no debe forzar un logout ni redirigir.
      if (error.status === 401 && !isPublicAuthRequest) {
        authService.clearSession();
        router.navigate(['/login']);
      }

      return throwError(() => error);
    })
  );
};
