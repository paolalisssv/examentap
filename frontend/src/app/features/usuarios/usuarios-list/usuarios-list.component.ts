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
import { UsuarioDetail } from '../models/usuario-detail.model';
import { Usuario } from '../models/usuario.model';
import { UsuarioService } from '../services/usuario.service';

@Component({
  selector: 'app-usuarios-list',
  imports: [DataTableComponent, ConfirmDialogComponent, ModalComponent, RouterLink],
  templateUrl: './usuarios-list.component.html',
  styleUrl: './usuarios-list.component.scss'
})
export class UsuariosListComponent implements OnInit {
  readonly columns: DataTableColumn<Usuario>[] = [
    { field: 'id', label: 'Código', sortable: true },
    { field: 'email', label: 'Usuario', sortable: true },
    { field: 'name', label: 'Nombre', sortable: true },
    {
      field: 'createdAt',
      label: 'Fecha de creación',
      sortable: true,
      formatter: (row) => formatDateTime(row.createdAt)
    }
  ];

  readonly rows = signal<Usuario[]>([]);
  readonly total = signal(0);
  readonly page = signal(1);
  readonly pageSize = 10;
  readonly sortField = signal('createdAt');
  readonly sortDirection = signal<'asc' | 'desc'>('desc');
  readonly loading = signal(false);
  readonly search = signal('');

  readonly deleteTarget = signal<Usuario | null>(null);
  readonly detail = signal<UsuarioDetail | null>(null);
  readonly detailOpen = signal(false);
  readonly successMessage = signal<string | null>(null);

  constructor(
    private readonly usuarioService: UsuarioService,
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

    this.usuarioService
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

  onView(usuario: Usuario): void {
    this.usuarioService.get(usuario.id).subscribe((detail) => {
      this.detail.set(detail);
      this.detailOpen.set(true);
    });
  }

  onEdit(usuario: Usuario): void {
    this.router.navigate(['/usuarios', usuario.id, 'editar']);
  }

  onDeleteRequest(usuario: Usuario): void {
    this.deleteTarget.set(usuario);
  }

  confirmDelete(): void {
    const target = this.deleteTarget();

    if (!target) {
      return;
    }

    this.usuarioService.delete(target.id).subscribe(() => {
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
    this.usuarioService
      .exportPdf(this.search() || undefined)
      .subscribe((blob) => downloadFile(blob, 'usuarios.pdf'));
  }

  exportExcel(): void {
    this.usuarioService
      .exportExcel(this.search() || undefined)
      .subscribe((blob) => downloadFile(blob, 'usuarios.xlsx'));
  }

  formatDate(iso: string): string {
    return formatDateTime(iso);
  }

  private mapSortField(field: string): string {
    return field === 'createdAt' ? 'created_at' : field;
  }
}
