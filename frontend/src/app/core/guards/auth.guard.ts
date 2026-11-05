import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map } from 'rxjs';
import { AuthService } from '../services/auth.service';

/** Solo usuarios autenticados. */
export const authGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);
  return auth.loadUser().pipe(map((ok) => ok || router.createUrlTree(['/login'])));
};

/** Solo visitantes (ej. login). */
export const guestGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);
  return auth.loadUser().pipe(map((ok) => (ok ? router.createUrlTree(['/']) : true)));
};

/** Solo administradores. Debe ir después de authGuard. */
export const adminGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);
  return auth.isAdmin() || router.createUrlTree(['/']);
};
