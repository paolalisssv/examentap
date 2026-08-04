import { ErrorHandler, Injectable } from '@angular/core';

import { isProductionEnvironment } from '../utils/environment.util';

@Injectable({
  providedIn: 'root'
})
export class GlobalErrorHandlerService implements ErrorHandler {
  handleError(error: unknown): void {
    if (isProductionEnvironment()) {
      console.error('Application error');

      return;
    }

    console.error(error);
  }
}
