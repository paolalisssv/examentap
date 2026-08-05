import { HttpErrorResponse } from '@angular/common/http';
import { Component, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { ApiResponse } from '../../../core/models/api-response.model';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-forgot-password',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './forgot-password.component.html',
  styleUrl: './forgot-password.component.scss'
})
export class ForgotPasswordComponent {
  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  readonly form;

  constructor(
    private readonly formBuilder: FormBuilder,
    private readonly authService: AuthService
  ) {
    this.form = this.formBuilder.nonNullable.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  get email() {
    return this.form.controls.email;
  }

  submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();

      return;
    }

    this.errorMessage.set(null);
    this.successMessage.set(null);
    this.loading.set(true);

    this.authService.forgotPassword(this.form.getRawValue()).subscribe({
      next: (message) => {
        this.loading.set(false);
        this.successMessage.set(message);
        this.form.reset();
      },
      error: (error: HttpErrorResponse) => {
        this.loading.set(false);
        this.errorMessage.set(this.extractMessage(error));
      }
    });
  }

  private extractMessage(error: HttpErrorResponse): string {
    const body = error.error as ApiResponse<unknown> | undefined;

    return (
      body?.message ||
      'Ocurrió un error al solicitar la recuperación de contraseña. Inténtalo nuevamente.'
    );
  }
}
