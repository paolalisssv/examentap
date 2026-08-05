import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-confirm-dialog',
  imports: [],
  templateUrl: './confirm-dialog.component.html',
  styleUrl: './confirm-dialog.component.scss'
})
export class ConfirmDialogComponent {
  @Input() open = false;
  @Input() title = 'Confirmar';
  @Input() message = '¿Estás seguro de realizar esta acción?';
  @Input() confirmLabel = 'Eliminar';
  @Input() cancelLabel = 'Cancelar';

  @Output() confirm = new EventEmitter<void>();
  @Output() dismiss = new EventEmitter<void>();
}
