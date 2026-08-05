import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { USUARIO_ENDPOINTS } from '../../../core/constants/api.constants';
import { PaginatedResult } from '../../../core/models/pagination.model';
import { ApiService } from '../../../core/services/api.service';
import { UsuarioDetail, UsuarioDetailDto, mapUsuarioDetail } from '../models/usuario-detail.model';
import { UsuarioFormPayload } from '../models/usuario-form-payload.model';
import { Usuario, UsuarioDto, mapUsuario } from '../models/usuario.model';

export interface UsuarioListParams {
  search?: string;
  sortField?: string;
  sortDirection?: 'asc' | 'desc';
  page: number;
  perPage: number;
}

@Injectable({
  providedIn: 'root'
})
export class UsuarioService {
  constructor(private readonly api: ApiService) {}

  list(params: UsuarioListParams): Observable<PaginatedResult<Usuario>> {
    const query = this.buildListQuery(params);

    return this.api
      .get<{ items: UsuarioDto[]; pagination: PaginatedResult<Usuario>['pagination'] }>(
        `${USUARIO_ENDPOINTS.base}?${query}`
      )
      .pipe(
        map((response) => ({
          items: (response.data?.items ?? []).map(mapUsuario),
          pagination: response.data?.pagination ?? {
            page: params.page,
            per_page: params.perPage,
            total: 0,
            total_pages: 0
          }
        }))
      );
  }

  get(id: string): Observable<UsuarioDetail> {
    return this.api
      .get<UsuarioDetailDto>(`${USUARIO_ENDPOINTS.base}/${id}`)
      .pipe(map((response) => mapUsuarioDetail(response.data as UsuarioDetailDto)));
  }

  create(payload: UsuarioFormPayload): Observable<Usuario> {
    return this.api
      .post<UsuarioDto>(USUARIO_ENDPOINTS.base, this.toFormData(payload))
      .pipe(map((response) => mapUsuario(response.data as UsuarioDto)));
  }

  update(id: string, payload: UsuarioFormPayload): Observable<Usuario> {
    const formData = this.toFormData(payload);
    formData.set('_method', 'PUT');

    return this.api
      .post<UsuarioDto>(`${USUARIO_ENDPOINTS.base}/${id}`, formData)
      .pipe(map((response) => mapUsuario(response.data as UsuarioDto)));
  }

  delete(id: string): Observable<void> {
    return this.api.delete<null>(`${USUARIO_ENDPOINTS.base}/${id}`).pipe(map(() => undefined));
  }

  exportPdf(search?: string): Observable<Blob> {
    return this.api.getBlob(USUARIO_ENDPOINTS.exportPdf, search ? { search } : undefined);
  }

  exportExcel(search?: string): Observable<Blob> {
    return this.api.getBlob(USUARIO_ENDPOINTS.exportExcel, search ? { search } : undefined);
  }

  private buildListQuery(params: UsuarioListParams): string {
    const query = new URLSearchParams();

    if (params.search) {
      query.set('search', params.search);
    }

    query.set('sort_field', params.sortField ?? 'created_at');
    query.set('sort_direction', params.sortDirection ?? 'desc');
    query.set('page', String(params.page));
    query.set('per_page', String(params.perPage));

    return query.toString();
  }

  private toFormData(payload: UsuarioFormPayload): FormData {
    const formData = new FormData();

    formData.set('name', payload.name);
    formData.set('email', payload.email);

    if (payload.password) {
      formData.set('password', payload.password);
    }

    if (payload.telefono) {
      formData.set('telefono', payload.telefono);
    }

    if (payload.foto) {
      formData.set('foto', payload.foto);
    }

    (payload.perfiles ?? []).forEach((perfilId) => formData.append('perfiles[]', perfilId));

    return formData;
  }
}
