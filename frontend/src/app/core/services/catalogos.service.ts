import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable, shareReplay } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Municipio } from '../models/cliente.model';
import { Rol } from '../models/user.model';

@Injectable({ providedIn: 'root' })
export class CatalogosService {
  private http = inject(HttpClient);
  private api = environment.apiUrl;

  private roles$?: Observable<Rol[]>;

  /** Roles (solo admin). Se cachea durante la sesión. */
  roles(): Observable<Rol[]> {
    this.roles$ ??= this.http.get<{ data: Rol[] }>(`${this.api}/roles`).pipe(
      map((r) => r.data),
      shareReplay(1),
    );
    return this.roles$;
  }

  buscarMunicipios(search: string): Observable<Municipio[]> {
    const params = new HttpParams().set('search', search);
    return this.http.get<{ data: Municipio[] }>(`${this.api}/municipios`, { params }).pipe(map((r) => r.data));
  }
}
