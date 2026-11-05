import { HttpClient } from '@angular/common/http';
import { computed, inject, Injectable, signal } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, of, tap, map, catchError } from 'rxjs';
import { environment } from '../../../environments/environment';
import { LoginResponse, User } from '../models/user.model';

const TOKEN_KEY = 'iuris_token';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);

  private readonly _user = signal<User | null>(null);
  readonly user = this._user.asReadonly();
  readonly isAdmin = computed(() => this._user()?.rol === 'admin');

  get token(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  }

  login(email: string, password: string): Observable<User> {
    return this.http
      .post<LoginResponse>(`${environment.apiUrl}/auth/login`, { email, password })
      .pipe(
        tap((res) => {
          localStorage.setItem(TOKEN_KEY, res.token);
          this._user.set(res.user);
        }),
        map((res) => res.user),
      );
  }

  /** Carga el usuario actual si hay token. Devuelve false si no hay sesión válida. */
  loadUser(): Observable<boolean> {
    if (this._user()) return of(true);
    if (!this.token) return of(false);
    return this.http.get<{ data: User }>(`${environment.apiUrl}/auth/me`).pipe(
      tap((res) => this._user.set(res.data)),
      map(() => true),
      catchError(() => {
        this.clearSession();
        return of(false);
      }),
    );
  }

  logout(): void {
    const done = () => {
      this.clearSession();
      this.router.navigate(['/login']);
    };
    if (!this.token) return done();
    this.http.post(`${environment.apiUrl}/auth/logout`, {}).subscribe({ complete: done, error: done });
  }

  clearSession(): void {
    localStorage.removeItem(TOKEN_KEY);
    this._user.set(null);
  }
}
