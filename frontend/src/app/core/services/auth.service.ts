import { Injectable, computed, signal } from '@angular/core';
import { Observable, catchError, map, of, tap } from 'rxjs';

import {
  AUTH_ENDPOINTS,
  AUTH_PERMISOS_STORAGE_KEY,
  AUTH_TOKEN_STORAGE_KEY,
  AUTH_USER_STORAGE_KEY
} from '../constants/api.constants';
import { AuthResponse, AuthResponseDto } from '../../features/auth/models/auth-response.model';
import { ForgotPasswordRequest } from '../../features/auth/models/forgot-password-request.model';
import { LoginRequest } from '../../features/auth/models/login-request.model';
import { User } from '../../features/auth/models/user.model';
import { SeccionPermiso } from '../../features/perfiles/models/seccion-permiso.model';
import { ApiService } from './api.service';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly currentUserSignal = signal<User | null>(this.readStoredUser());
  private readonly permisosSignal = signal<SeccionPermiso[]>(this.readStoredPermisos());

  readonly currentUser = this.currentUserSignal.asReadonly();
  readonly permisos = this.permisosSignal.asReadonly();
  readonly isAuthenticated = computed(
    () => this.currentUserSignal() !== null && this.getToken() !== null
  );

  constructor(private readonly api: ApiService) {}

  login(credentials: LoginRequest): Observable<AuthResponse> {
    return this.api.post<AuthResponseDto>(AUTH_ENDPOINTS.login, credentials).pipe(
      map((response) => this.mapAuthResponse(response.data as AuthResponseDto)),
      tap((auth) => this.persistSession(auth))
    );
  }

  logout(): Observable<void> {
    return this.api.post<null>(AUTH_ENDPOINTS.logout, {}).pipe(
      map(() => undefined),
      catchError(() => of(undefined)),
      tap(() => this.clearSession())
    );
  }

  forgotPassword(payload: ForgotPasswordRequest): Observable<string> {
    return this.api
      .post<null>(AUTH_ENDPOINTS.forgotPassword, payload)
      .pipe(map((response) => response.message));
  }

  restoreSession(): Observable<boolean> {
    if (!this.getToken()) {
      return of(false);
    }

    return this.api.get<{ user: User; permisos: SeccionPermiso[] }>(AUTH_ENDPOINTS.me).pipe(
      tap((response) => {
        this.currentUserSignal.set(response.data?.user ?? null);
        this.setPermisos(response.data?.permisos ?? []);
      }),
      map(() => true),
      catchError(() => {
        this.clearSession();

        return of(false);
      })
    );
  }

  tienePermiso(seccion: string, permiso: keyof Omit<SeccionPermiso, 'seccion'>): boolean {
    const entry = this.permisosSignal().find((item) => item.seccion === seccion);

    return entry ? entry[permiso] : false;
  }

  getToken(): string | null {
    return localStorage.getItem(AUTH_TOKEN_STORAGE_KEY);
  }

  clearSession(): void {
    localStorage.removeItem(AUTH_TOKEN_STORAGE_KEY);
    localStorage.removeItem(AUTH_USER_STORAGE_KEY);
    localStorage.removeItem(AUTH_PERMISOS_STORAGE_KEY);
    this.currentUserSignal.set(null);
    this.permisosSignal.set([]);
  }

  private persistSession(auth: AuthResponse): void {
    localStorage.setItem(AUTH_TOKEN_STORAGE_KEY, auth.token);
    localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(auth.user));
    this.currentUserSignal.set(auth.user);
    this.setPermisos(auth.permisos);
  }

  private setPermisos(permisos: SeccionPermiso[]): void {
    localStorage.setItem(AUTH_PERMISOS_STORAGE_KEY, JSON.stringify(permisos));
    this.permisosSignal.set(permisos);
  }

  private mapAuthResponse(dto: AuthResponseDto): AuthResponse {
    return {
      user: dto.user,
      token: dto.token,
      tokenType: dto.token_type,
      expiresAt: dto.expires_at,
      permisos: dto.permisos
    };
  }

  private readStoredUser(): User | null {
    const raw = localStorage.getItem(AUTH_USER_STORAGE_KEY);

    return raw ? (JSON.parse(raw) as User) : null;
  }

  private readStoredPermisos(): SeccionPermiso[] {
    const raw = localStorage.getItem(AUTH_PERMISOS_STORAGE_KEY);

    return raw ? (JSON.parse(raw) as SeccionPermiso[]) : [];
  }
}
