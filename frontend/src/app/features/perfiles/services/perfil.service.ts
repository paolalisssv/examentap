import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { PERFIL_ENDPOINTS } from '../../../core/constants/api.constants';
import { PaginatedResult } from '../../../core/models/pagination.model';
import { ApiService } from '../../../core/services/api.service';
import { PerfilFormPayload } from '../models/perfil-form-payload.model';
import { Perfil, PerfilDto, mapPerfil } from '../models/perfil.model';

export interface PerfilListParams {
  search?: string;
  sortField?: string;
  sortDirection?: 'asc' | 'desc';
  page: number;
  perPage: number;
}

@Injectable({
  providedIn: 'root'
})
export class PerfilService {
  constructor(private readonly api: ApiService) {}

  list(params: PerfilListParams): Observable<PaginatedResult<Perfil>> {
    const query = this.buildListQuery(params);

    return this.api
      .get<{ items: PerfilDto[]; pagination: PaginatedResult<Perfil>['pagination'] }>(
        `${PERFIL_ENDPOINTS.base}?${query}`
      )
      .pipe(
        map((response) => ({
          items: (response.data?.items ?? []).map(mapPerfil),
          pagination: response.data?.pagination ?? {
            page: params.page,
            per_page: params.perPage,
            total: 0,
            total_pages: 0
          }
        }))
      );
  }

  all(): Observable<Perfil[]> {
    return this.list({ page: 1, perPage: 100 }).pipe(map((result) => result.items));
  }

  get(id: string): Observable<Perfil> {
    return this.api
      .get<PerfilDto>(`${PERFIL_ENDPOINTS.base}/${id}`)
      .pipe(map((response) => mapPerfil(response.data as PerfilDto)));
  }

  create(payload: PerfilFormPayload): Observable<Perfil> {
    return this.api
      .post<PerfilDto>(PERFIL_ENDPOINTS.base, payload)
      .pipe(map((response) => mapPerfil(response.data as PerfilDto)));
  }

  update(id: string, payload: PerfilFormPayload): Observable<Perfil> {
    return this.api
      .put<PerfilDto>(`${PERFIL_ENDPOINTS.base}/${id}`, payload)
      .pipe(map((response) => mapPerfil(response.data as PerfilDto)));
  }

  delete(id: string): Observable<void> {
    return this.api.delete<null>(`${PERFIL_ENDPOINTS.base}/${id}`).pipe(map(() => undefined));
  }

  exportPdf(search?: string): Observable<Blob> {
    return this.api.getBlob(PERFIL_ENDPOINTS.exportPdf, search ? { search } : undefined);
  }

  exportExcel(search?: string): Observable<Blob> {
    return this.api.getBlob(PERFIL_ENDPOINTS.exportExcel, search ? { search } : undefined);
  }

  private buildListQuery(params: PerfilListParams): string {
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
}
