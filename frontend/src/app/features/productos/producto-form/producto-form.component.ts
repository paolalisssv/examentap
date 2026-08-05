import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, computed, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { ApiResponse } from '../../../core/models/api-response.model';
import { ProductoFormPayload } from '../models/producto-form-payload.model';
import { ProductoService } from '../services/producto.service';

const PRECIO_PATTERN = /^\d{1,3}(\.\d{1,2})?$/;

@Component({
  selector: 'app-producto-form',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './producto-form.component.html',
  styleUrl: './producto-form.component.scss'
})
export class ProductoFormComponent implements OnInit {
  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly productoId = signal<string | null>(null);
  readonly isEditMode = computed(() => this.productoId() !== null);

  readonly form;

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly productoService: ProductoService,
    private readonly router: Router,
    private readonly route: ActivatedRoute
  ) {
    this.form = this.formBuilder.nonNullable.group({
      name: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(255)]],
      precio: ['', [Validators.required, Validators.pattern(PRECIO_PATTERN)]]
    });
  }

  get name() {
    return this.form.controls.name;
  }

  get precio() {
    return this.form.controls.precio;
  }

  // El $ es solo visual (span aparte); el control conserva únicamente el número,
  // normalizado a dos decimales al salir del campo.
  formatPrecio(): void {
    const value = Number(this.precio.value);

    if (this.precio.value !== '' && !Number.isNaN(value)) {
      this.precio.setValue(value.toFixed(2));
    }
  }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');

    if (id) {
      this.productoId.set(id);
      this.loadProducto(id);
    }
  }

  submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();

      return;
    }

    this.errorMessage.set(null);
    this.loading.set(true);

    const raw = this.form.getRawValue();
    const payload: ProductoFormPayload = {
      name: raw.name,
      precio: Number(raw.precio)
    };

    const request = this.isEditMode()
      ? this.productoService.update(this.productoId() as string, payload)
      : this.productoService.create(payload);

    request.subscribe({
      next: () => {
        this.loading.set(false);
        this.router.navigate(['/productos'], {
          queryParams: {
            mensaje: this.isEditMode()
              ? 'Producto actualizado correctamente.'
              : 'Producto creado correctamente.'
          }
        });
      },
      error: (error: HttpErrorResponse) => {
        this.loading.set(false);
        this.errorMessage.set(this.extractMessage(error));
      }
    });
  }

  private loadProducto(id: string): void {
    this.loading.set(true);

    this.productoService.get(id).subscribe({
      next: (producto) => {
        this.form.patchValue({
          name: producto.name,
          precio: producto.precio.toString()
        });
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }

  private extractMessage(error: HttpErrorResponse): string {
    const body = error.error as ApiResponse<unknown> | undefined;

    return body?.message || 'Ocurrió un error al guardar el producto. Inténtalo nuevamente.';
  }
}
