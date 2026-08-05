import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';

import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-sin-acceso',
  imports: [],
  templateUrl: './sin-acceso.component.html',
  styleUrl: './sin-acceso.component.scss'
})
export class SinAccesoComponent {
  readonly message: string;

  constructor(
    private readonly route: ActivatedRoute,
    private readonly authService: AuthService,
    private readonly router: Router
  ) {
    this.message =
      this.route.snapshot.queryParamMap.get('error') ??
      'No tienes permisos para acceder a este módulo.';
  }

  logout(): void {
    this.authService.logout().subscribe(() => this.router.navigate(['/login']));
  }
}
