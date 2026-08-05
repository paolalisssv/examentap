import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, computed, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { ApiResponse } from '../../../core/models/api-response.model';
import { Perfil } from '../../perfiles/models/perfil.model';
import { PerfilService } from '../../perfiles/services/perfil.service';
import { UsuarioFormPayload } from '../models/usuario-form-payload.model';
import { UsuarioService } from '../services/usuario.service';

const PASSWORD_PATTERN = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;
const TELEFONO_PATTERN = /^\+?[0-9]{7,15}$/;

@Component({
  selector: 'app-usuario-form',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './usuario-form.component.html',
  styleUrl: './usuario-form.component.scss'
})
export class UsuarioFormComponent implements OnInit {
  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly fotoPreview = signal<string | null>(null);
  readonly fotoError = signal<string | null>(null);
  readonly perfilesDisponibles = signal<Perfil[]>([]);
  readonly usuarioId = signal<string | null>(null);
  readonly isEditMode = computed(() => this.usuarioId() !== null);
  readonly isBootstrap: boolean;

  readonly form;

  private selectedFoto: File | null = null;

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly usuarioService: UsuarioService,
    private readonly perfilService: PerfilService,
    private readonly router: Router,
    private readonly route: ActivatedRoute
  ) {
    this.isBootstrap = this.route.snapshot.data['bootstrap'] === true;

    this.form = this.formBuilder.nonNullable.group({
      name: ['', [Validators.required, Validators.maxLength(255)]],
      email: ['', [Validators.required, Validators.email]],
      password: [''],
      telefono: ['', [Validators.pattern(TELEFONO_PATTERN)]],
      perfiles: this.formBuilder.nonNullable.control<string[]>([])
    });
  }

  ngOnInit(): void {
    if (!this.isBootstrap) {
      this.perfilService.all().subscribe((perfiles) => this.perfilesDisponibles.set(perfiles));
    }

    const id = this.route.snapshot.paramMap.get('id');

    if (id) {
      this.usuarioId.set(id);
      this.loadUsuario(id);
    } else {
      this.form.controls.password.addValidators([
        Validators.required,
        Validators.pattern(PASSWORD_PATTERN)
      ]);
    }

    this.form.controls.password.updateValueAndValidity();
  }

  get name() {
    return this.form.controls.name;
  }

  get email() {
    return this.form.controls.email;
  }

  get password() {
    return this.form.controls.password;
  }

  get telefono() {
    return this.form.controls.telefono;
  }

  onFotoSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
      return;
    }

    if (!['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)) {
      this.fotoError.set('El archivo debe ser una imagen jpg, jpeg, png o webp.');
      return;
    }

    if (file.size > 2 * 1024 * 1024) {
      this.fotoError.set('La foto no debe superar los 2MB.');
      return;
    }

    this.fotoError.set(null);
    this.selectedFoto = file;

    const reader = new FileReader();
    reader.onload = () => this.fotoPreview.set(reader.result as string);
    reader.readAsDataURL(file);
  }

  togglePerfil(perfilId: string, checked: boolean): void {
    const current = this.form.controls.perfiles.value;
    const next = checked ? [...current, perfilId] : current.filter((id) => id !== perfilId);

    this.form.controls.perfiles.setValue(next);
  }

  isPerfilSelected(perfilId: string): boolean {
    return this.form.controls.perfiles.value.includes(perfilId);
  }

  submit(): void {
    const requiresFoto = !this.isEditMode() && !this.selectedFoto;

    if (this.form.invalid || requiresFoto) {
      this.form.markAllAsTouched();

      if (requiresFoto) {
        this.fotoError.set('La foto de perfil es obligatoria.');
      }

      return;
    }

    this.errorMessage.set(null);
    this.loading.set(true);

    const raw = this.form.getRawValue();
    const payload: UsuarioFormPayload = {
      name: raw.name,
      email: raw.email,
      telefono: raw.telefono || undefined,
      perfiles: raw.perfiles,
      ...(raw.password ? { password: raw.password } : {}),
      ...(this.selectedFoto ? { foto: this.selectedFoto } : {})
    };

    const request = this.isEditMode()
      ? this.usuarioService.update(this.usuarioId() as string, payload)
      : this.usuarioService.create(payload);

    request.subscribe({
      next: () => {
        this.loading.set(false);

        if (this.isBootstrap) {
          this.router.navigate(['/login'], {
            queryParams: {
              mensaje: 'Usuario administrador creado correctamente. Inicia sesión para continuar.'
            }
          });

          return;
        }

        this.router.navigate(['/usuarios'], {
          queryParams: {
            mensaje: this.isEditMode()
              ? 'Usuario actualizado correctamente.'
              : 'Usuario creado correctamente.'
          }
        });
      },
      error: (error: HttpErrorResponse) => {
        this.loading.set(false);
        this.errorMessage.set(this.extractMessage(error));
      }
    });
  }

  private loadUsuario(id: string): void {
    this.loading.set(true);

    this.usuarioService.get(id).subscribe({
      next: (detail) => {
        this.form.patchValue({
          name: detail.usuario.name,
          email: detail.usuario.email,
          telefono: detail.usuario.telefono ?? '',
          perfiles: detail.usuario.perfiles
        });
        this.fotoPreview.set(detail.usuario.fotoUrl || null);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }

  private extractMessage(error: HttpErrorResponse): string {
    const body = error.error as ApiResponse<unknown> | undefined;

    return body?.message || 'Ocurrió un error al guardar el usuario. Inténtalo nuevamente.';
  }
}
