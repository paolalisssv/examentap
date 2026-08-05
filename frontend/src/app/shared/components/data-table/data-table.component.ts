import { Component, EventEmitter, Input, Output } from '@angular/core';

import { DataTableColumn, DataTableSort } from './data-table.model';

@Component({
  selector: 'app-data-table',
  imports: [],
  templateUrl: './data-table.component.html',
  styleUrl: './data-table.component.scss'
})
export class DataTableComponent<T extends { id: string }> {
  @Input({ required: true }) columns: DataTableColumn<T>[] = [];
  @Input({ required: true }) rows: T[] = [];
  @Input() total = 0;
  @Input() page = 1;
  @Input() pageSize = 10;
  @Input() sortField = '';
  @Input() sortDirection: 'asc' | 'desc' = 'desc';
  @Input() loading = false;
  @Input() canEdit = true;
  @Input() canDelete = true;

  @Output() pageChange = new EventEmitter<number>();
  @Output() sortChange = new EventEmitter<DataTableSort>();
  @Output() searchChange = new EventEmitter<string>();
  @Output() view = new EventEmitter<T>();
  @Output() edit = new EventEmitter<T>();
  @Output() delete = new EventEmitter<T>();

  private searchTimeout?: ReturnType<typeof setTimeout>;

  get totalPages(): number {
    return this.pageSize > 0 ? Math.max(1, Math.ceil(this.total / this.pageSize)) : 1;
  }

  cellValue(row: T, column: DataTableColumn<T>): string {
    if (column.formatter) {
      return column.formatter(row);
    }

    const value = (row as unknown as Record<string, unknown>)[column.field];

    return value === null || value === undefined ? '' : String(value);
  }

  sortBy(column: DataTableColumn<T>): void {
    if (!column.sortable) {
      return;
    }

    const direction =
      this.sortField === column.field && this.sortDirection === 'asc' ? 'desc' : 'asc';
    this.sortChange.emit({ field: column.field, direction });
  }

  goToPage(page: number): void {
    if (page < 1 || page > this.totalPages || page === this.page) {
      return;
    }

    this.pageChange.emit(page);
  }

  onSearchInput(value: string): void {
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => this.searchChange.emit(value), 350);
  }
}
