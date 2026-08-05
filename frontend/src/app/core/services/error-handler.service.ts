import { ErrorHandler, Injectable } from '@angular/core';

import { isProductionEnvironment } from '../utils/environment.util';

@Injectable({
  providedIn: 'root'
})
export class GlobalErrorHandlerService implements ErrorHandler {
  handleError(error: unknown): void {
    // En producción se oculta el detalle del error para no exponer información
    // interna (mensajes, stack traces) en la consola del navegador.
    if (isProductionEnvironment()) {
      console.error('Application error');

      return;
    }

    console.error(error);
  }
}
