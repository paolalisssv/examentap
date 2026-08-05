import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, computed, signal } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { SECCION_LABELS, SECCIONES_SISTEMA } from '../../../core/constants/secciones.constants';
import { ApiResponse } from '../../../core/models/api-response.model';
import { PerfilFormPayload } from '../models/perfil-form-payload.model';
import { PerfilService } from '../services/perfil.service';

@Component({
  selector: 'app-perfil-form',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './perfil-form.component.html',
  styleUrl: './perfil-form.component.scss'
})
export class PerfilFormComponent implements OnInit {
  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly perfilId = signal<string | null>(null);
  readonly isEditMode = computed(() => this.perfilId() !== null);
  readonly secciones = SECCIONES_SISTEMA;
  readonly seccionLabels = SECCION_LABELS;

  readonly form;

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly perfilService: PerfilService,
    private readonly router: Router,
    private readonly route: ActivatedRoute
  ) {
    this.form = this.formBuilder.group({
      name: this.formBuilder.nonNullable.control('', [
        Validators.required,
        Validators.maxLength(255)
      ]),
      secciones: this.formBuilder.group(
        Object.fromEntries(
          SECCIONES_SISTEMA.map((seccion) => [
            seccion,
            this.formBuilder.nonNullable.group({
              crear: this.formBuilder.nonNullable.control(false),
              consultar: this.formBuilder.nonNullable.control(false),
              editar: this.formBuilder.nonNullable.control(false),
              eliminar: this.formBuilder.nonNullable.control(false)
            })
          ])
        )
      )
    });
  }

  get name() {
    return this.form.controls.name;
  }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');

    if (id) {
      this.perfilId.set(id);
      this.loadPerfil(id);
    }
  }

  seccionGroup(seccion: string): FormGroup {
    return (this.form.controls.secciones as FormGroup).controls[seccion] as FormGroup;
  }

  submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();

      return;
    }

    this.errorMessage.set(null);
    this.loading.set(true);

    const raw = this.form.getRawValue();
    const payload: PerfilFormPayload = {
      name: raw.name as string,
      secciones: SECCIONES_SISTEMA.map((seccion) => ({
        seccion,
        ...(
          raw.secciones as Record<
            string,
            { crear: boolean; consultar: boolean; editar: boolean; eliminar: boolean }
          >
        )[seccion]
      }))
    };

    const request = this.isEditMode()
      ? this.perfilService.update(this.perfilId() as string, payload)
      : this.perfilService.create(payload);

    request.subscribe({
      next: () => {
        this.loading.set(false);
        this.router.navigate(['/perfiles'], {
          queryParams: {
            mensaje: this.isEditMode()
              ? 'Perfil actualizado correctamente.'
              : 'Perfil creado correctamente.'
          }
        });
      },
      error: (error: HttpErrorResponse) => {
        this.loading.set(false);
        this.errorMessage.set(this.extractMessage(error));
      }
    });
  }

  private loadPerfil(id: string): void {
    this.loading.set(true);

    this.perfilService.get(id).subscribe({
      next: (perfil) => {
        this.form.patchValue({ name: perfil.name });

        perfil.secciones.forEach((entry) => {
          const group = this.seccionGroup(entry.seccion);

          if (group) {
            group.patchValue({
              crear: entry.crear,
              consultar: entry.consultar,
              editar: entry.editar,
              eliminar: entry.eliminar
            });
          }
        });

        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }

  private extractMessage(error: HttpErrorResponse): string {
    const body = error.error as ApiResponse<unknown> | undefined;

    return body?.message || 'Ocurrió un error al guardar el perfil. Inténtalo nuevamente.';
  }
}
