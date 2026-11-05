import { Component, inject } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-dashboard',
  template: `
    <div class="welcome">
      Bienvenido, {{ saludo[auth.user()?.rol ?? 'abogado'] }}
      <strong>{{ auth.user()?.nombre_completo }}</strong> 🎉
    </div>
  `,
  styles: `
    .welcome {
      padding: 1.25rem;
      border-radius: 12px;
      text-align: center;
      color: #fff;
      background: linear-gradient(90deg, var(--p-primary-600), #111);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
  `,
})
export class DashboardComponent {
  auth = inject(AuthService);
  saludo: Record<string, string> = { admin: 'administrador', abogado: 'abogad@', pasante: 'pasante' };
}
