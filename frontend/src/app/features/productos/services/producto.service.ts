import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { PRODUCTO_ENDPOINTS } from '../../../core/constants/api.constants';
import { PaginatedResult } from '../../../core/models/pagination.model';
import { ApiService } from '../../../core/services/api.service';
import { ProductoFormPayload } from '../models/producto-form-payload.model';
import { Producto, ProductoDto, mapProducto } from '../models/producto.model';

export interface ProductoListParams {
  search?: string;
  sortField?: string;
  sortDirection?: 'asc' | 'desc';
  page: number;
  perPage: number;
}

@Injectable({
  providedIn: 'root'
})
export class ProductoService {
  constructor(private readonly api: ApiService) {}

  list(params: ProductoListParams): Observable<PaginatedResult<Producto>> {
    const query = this.buildListQuery(params);

    return this.api
      .get<{ items: ProductoDto[]; pagination: PaginatedResult<Producto>['pagination'] }>(
        `${PRODUCTO_ENDPOINTS.base}?${query}`
      )
      .pipe(
        map((response) => ({
          items: (response.data?.items ?? []).map(mapProducto),
          pagination: response.data?.pagination ?? {
            page: params.page,
            per_page: params.perPage,
            total: 0,
            total_pages: 0
          }
        }))
      );
  }

  get(id: string): Observable<Producto> {
    return this.api
      .get<ProductoDto>(`${PRODUCTO_ENDPOINTS.base}/${id}`)
      .pipe(map((response) => mapProducto(response.data as ProductoDto)));
  }

  create(payload: ProductoFormPayload): Observable<Producto> {
    return this.api
      .post<ProductoDto>(PRODUCTO_ENDPOINTS.base, payload)
      .pipe(map((response) => mapProducto(response.data as ProductoDto)));
  }

  update(id: string, payload: ProductoFormPayload): Observable<Producto> {
    return this.api
      .put<ProductoDto>(`${PRODUCTO_ENDPOINTS.base}/${id}`, payload)
      .pipe(map((response) => mapProducto(response.data as ProductoDto)));
  }

  delete(id: string): Observable<void> {
    return this.api.delete<null>(`${PRODUCTO_ENDPOINTS.base}/${id}`).pipe(map(() => undefined));
  }

  exportPdf(search?: string): Observable<Blob> {
    return this.api.getBlob(PRODUCTO_ENDPOINTS.exportPdf, search ? { search } : undefined);
  }

  exportExcel(search?: string): Observable<Blob> {
    return this.api.getBlob(PRODUCTO_ENDPOINTS.exportExcel, search ? { search } : undefined);
  }

  private buildListQuery(params: ProductoListParams): string {
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
