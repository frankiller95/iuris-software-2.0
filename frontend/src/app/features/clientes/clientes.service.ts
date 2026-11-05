import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Cliente, ClientePayload, TipoPersona } from '../../core/models/cliente.model';
import { Paginated } from '../../core/models/user.model';

export interface ClientesFiltro {
  page: number;
  per_page: number;
  search?: string;
  tipo_persona?: TipoPersona | null;
  activo?: boolean | null;
}

@Injectable({ providedIn: 'root' })
export class ClientesService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/clientes`;

  list(filtro: ClientesFiltro): Observable<Paginated<Cliente>> {
    let params = new HttpParams().set('page', filtro.page).set('per_page', filtro.per_page);
    if (filtro.search) params = params.set('search', filtro.search);
    if (filtro.tipo_persona) params = params.set('tipo_persona', filtro.tipo_persona);
    if (filtro.activo !== null && filtro.activo !== undefined) params = params.set('activo', filtro.activo ? 1 : 0);
    return this.http.get<Paginated<Cliente>>(this.url, { params });
  }

  create(payload: ClientePayload): Observable<{ data: Cliente }> {
    return this.http.post<{ data: Cliente }>(this.url, payload);
  }

  update(id: number, payload: ClientePayload): Observable<{ data: Cliente }> {
    return this.http.put<{ data: Cliente }>(`${this.url}/${id}`, payload);
  }

  toggleEstado(id: number): Observable<{ data: Cliente }> {
    return this.http.patch<{ data: Cliente }>(`${this.url}/${id}/estado`, {});
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
