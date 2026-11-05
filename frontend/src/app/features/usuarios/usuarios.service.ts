import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Paginated, User, UserPayload } from '../../core/models/user.model';

@Injectable({ providedIn: 'root' })
export class UsuariosService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/usuarios`;

  list(params: { page: number; per_page: number; search?: string }): Observable<Paginated<User>> {
    let httpParams = new HttpParams().set('page', params.page).set('per_page', params.per_page);
    if (params.search) httpParams = httpParams.set('search', params.search);
    return this.http.get<Paginated<User>>(this.url, { params: httpParams });
  }

  create(payload: UserPayload): Observable<{ data: User }> {
    return this.http.post<{ data: User }>(this.url, payload);
  }

  update(id: number, payload: UserPayload): Observable<{ data: User }> {
    return this.http.put<{ data: User }>(`${this.url}/${id}`, payload);
  }

  toggleEstado(id: number): Observable<{ data: User }> {
    return this.http.patch<{ data: User }>(`${this.url}/${id}/estado`, {});
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
