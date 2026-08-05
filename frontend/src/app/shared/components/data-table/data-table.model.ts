export interface DataTableColumn<T> {
  field: string;
  label: string;
  sortable?: boolean;
  formatter?: (row: T) => string;
}

export interface DataTableSort {
  field: string;
  direction: 'asc' | 'desc';
}
