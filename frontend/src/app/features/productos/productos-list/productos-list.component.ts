import { Component, OnInit, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { AuthService } from '../../../core/services/auth.service';
import { ConfirmDialogComponent } from '../../../shared/components/confirm-dialog/confirm-dialog.component';
import {
  DataTableColumn,
  DataTableSort
} from '../../../shared/components/data-table/data-table.model';
import { DataTableComponent } from '../../../shared/components/data-table/data-table.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';
import { downloadFile } from '../../../shared/utils/download-file.util';
import { formatDateTime } from '../../../shared/utils/format-date.util';
import { Producto } from '../models/producto.model';
import { ProductoService } from '../services/producto.service';

@Component({
  selector: 'app-productos-list',
  imports: [DataTableComponent, ConfirmDialogComponent, ModalComponent, RouterLink],
  templateUrl: './productos-list.component.html',
  styleUrl: './productos-list.component.scss'
})
export class ProductosListComponent implements OnInit {
  readonly columns: DataTableColumn<Producto>[] = [
    { field: 'id', label: 'Código', sortable: true },
    { field: 'name', label: 'Nombre', sortable: true },
    { field: 'precio', label: 'Precio', sortable: true, formatter: (row) => `$ ${row.precio.toFixed(2)}` },
    {
      field: 'createdAt',
      label: 'Fecha de creación',
      sortable: true,
      formatter: (row) => formatDateTime(row.createdAt)
    }
  ];

  readonly rows = signal<Producto[]>([]);
  readonly total = signal(0);
  readonly page = signal(1);
  readonly pageSize = 10;
  readonly sortField = signal('createdAt');
  readonly sortDirection = signal<'asc' | 'desc'>('desc');
  readonly loading = signal(false);
  readonly search = signal('');

  readonly deleteTarget = signal<Producto | null>(null);
  readonly detail = signal<Producto | null>(null);
  readonly detailOpen = signal(false);
  readonly successMessage = signal<string | null>(null);

  constructor(
    private readonly productoService: ProductoService,
    private readonly router: Router,
    private readonly route: ActivatedRoute,
    readonly authService: AuthService
  ) {}

  ngOnInit(): void {
    this.successMessage.set(this.route.snapshot.queryParamMap.get('mensaje'));
    this.load();
  }

  load(): void {
    this.loading.set(true);

    this.productoService
      .list({
        search: this.search() || undefined,
        sortField: this.mapSortField(this.sortField()),
        sortDirection: this.sortDirection(),
        page: this.page(),
        perPage: this.pageSize
      })
      .subscribe({
        next: (result) => {
          this.rows.set(result.items);
          this.total.set(result.pagination.total);
          this.loading.set(false);
        },
        error: () => this.loading.set(false)
      });
  }

  onSearch(value: string): void {
    this.search.set(value);
    this.page.set(1);
    this.load();
  }

  onSort(sort: DataTableSort): void {
    this.sortField.set(sort.field);
    this.sortDirection.set(sort.direction);
    this.load();
  }

  onPageChange(page: number): void {
    this.page.set(page);
    this.load();
  }

  onView(producto: Producto): void {
    this.detail.set(producto);
    this.detailOpen.set(true);
  }

  onEdit(producto: Producto): void {
    this.router.navigate(['/productos', producto.id, 'editar']);
  }

  onDeleteRequest(producto: Producto): void {
    this.deleteTarget.set(producto);
  }

  confirmDelete(): void {
    const target = this.deleteTarget();

    if (!target) {
      return;
    }

    this.productoService.delete(target.id).subscribe(() => {
      this.deleteTarget.set(null);
      this.load();
    });
  }

  cancelDelete(): void {
    this.deleteTarget.set(null);
  }

  closeDetail(): void {
    this.detailOpen.set(false);
  }

  exportPdf(): void {
    this.productoService
      .exportPdf(this.search() || undefined)
      .subscribe((blob) => downloadFile(blob, 'productos.pdf'));
  }

  exportExcel(): void {
    this.productoService
      .exportExcel(this.search() || undefined)
      .subscribe((blob) => downloadFile(blob, 'productos.xlsx'));
  }

  formatDate(iso: string): string {
    return formatDateTime(iso);
  }

  private mapSortField(field: string): string {
    return field === 'createdAt' ? 'created_at' : field;
  }
}
