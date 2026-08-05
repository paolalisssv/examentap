import { Injectable, signal } from '@angular/core';
import { Observable, catchError, map, of, tap } from 'rxjs';

import { SYSTEM_ENDPOINTS } from '../constants/api.constants';
import { SystemStatus } from '../models/system-status.model';
import { ApiService } from './api.service';

@Injectable({
  providedIn: 'root'
})
export class SystemService {
  private readonly initializedSignal = signal<boolean | null>(null);

  readonly initialized = this.initializedSignal.asReadonly();

  constructor(private readonly api: ApiService) {}

  checkStatus(): Observable<boolean> {
    return this.api.get<SystemStatus>(SYSTEM_ENDPOINTS.status).pipe(
      map((response) => response.data?.initialized ?? true),
      tap((initialized) => this.initializedSignal.set(initialized)),
      // Si la consulta falla, se asume que el sistema ya está inicializado para
      // no exponer por error la pantalla pública de configuración inicial.
      catchError(() => {
        this.initializedSignal.set(true);

        return of(true);
      })
    );
  }
}
