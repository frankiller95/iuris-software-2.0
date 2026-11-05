import { Component, computed, inject, signal, viewChild } from '@angular/core';
import { FormBuilder, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { Subject, debounceTime, distinctUntilChanged } from 'rxjs';
import { takeUntilDestroyed, toSignal } from '@angular/core/rxjs-interop';
import { Table, TableLazyLoadEvent, TableModule } from 'primeng/table';
import { ButtonModule } from 'primeng/button';
import { InputTextModule } from 'primeng/inputtext';
import { IconFieldModule } from 'primeng/iconfield';
import { InputIconModule } from 'primeng/inputicon';
import { DialogModule } from 'primeng/dialog';
import { SelectModule } from 'primeng/select';
import { SelectButtonModule } from 'primeng/selectbutton';
import { AutoCompleteCompleteEvent, AutoCompleteModule } from 'primeng/autocomplete';
import { ToggleSwitchModule } from 'primeng/toggleswitch';
import { TagModule } from 'primeng/tag';
import { TooltipModule } from 'primeng/tooltip';
import { ConfirmationService, MessageService } from 'primeng/api';
import { Cliente, ClientePayload, Genero, Municipio, TipoPersona } from '../../core/models/cliente.model';
import { AuthService } from '../../core/services/auth.service';
import { CatalogosService } from '../../core/services/catalogos.service';
import { ClientesService } from './clientes.service';

@Component({
  selector: 'app-clientes',
  imports: [
    ReactiveFormsModule, FormsModule, TableModule, ButtonModule, InputTextModule, IconFieldModule, InputIconModule,
    DialogModule, SelectModule, SelectButtonModule, AutoCompleteModule, ToggleSwitchModule, TagModule, TooltipModule,
  ],
  templateUrl: './clientes.component.html',
  styleUrl: './clientes.component.scss',
})
export class ClientesComponent {
  private service = inject(ClientesService);
  private catalogos = inject(CatalogosService);
  private messages = inject(MessageService);
  private confirm = inject(ConfirmationService);
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);

  table = viewChild<Table>('dt');

  // Listado
  clientes = signal<Cliente[]>([]);
  total = signal(0);
  loading = signal(false);
  rows = 10;
  private search = '';
  private lastEvent: TableLazyLoadEvent = { first: 0, rows: this.rows };
  private search$ = new Subject<string>();
  filtroTipo: TipoPersona | null = null;
  filtroEstado: boolean | null = true;

  // Permisos
  puedeCambiarEstado = computed(() => ['admin', 'abogado'].includes(this.auth.user()?.rol ?? ''));
  puedeEliminar = computed(() => this.auth.isAdmin());

  // Opciones
  tiposPersona = [
    { label: 'Persona natural', value: 'natural' },
    { label: 'Empresa', value: 'juridica' },
  ];
  generos = [
    { label: 'Masculino', value: 'masculino' },
    { label: 'Femenino', value: 'femenino' },
  ];
  estados = [
    { label: 'Activos', value: true },
    { label: 'Inactivos', value: false },
  ];
  municipios = signal<Municipio[]>([]);

  // Formulario
  dialogVisible = signal(false);
  saving = signal(false);
  editing = signal<Cliente | null>(null);
  serverErrors = signal<Record<string, string[]>>({});

  form = this.fb.group({
    tipo_persona: this.fb.nonNullable.control<TipoPersona>('natural', Validators.required),
    numero_documento: this.fb.nonNullable.control('', [
      Validators.required,
      Validators.maxLength(20),
      Validators.pattern(/^[0-9][0-9.\-]*$/),
    ]),
    nombre: this.fb.nonNullable.control('', [Validators.required, Validators.maxLength(200)]),
    genero: this.fb.control<Genero | null>(null, Validators.required),
    municipio: this.fb.control<Municipio | null>(null, Validators.required),
    telefono: this.fb.nonNullable.control(''),
    email: this.fb.nonNullable.control('', Validators.email),
    direccion: this.fb.nonNullable.control(''),
    activo: this.fb.nonNullable.control(true),
  });

  tipoPersona = toSignal(this.form.controls.tipo_persona.valueChanges, { initialValue: this.form.controls.tipo_persona.value });
  esNatural = computed(() => this.tipoPersona() === 'natural');

  constructor() {
    this.search$.pipe(debounceTime(400), distinctUntilChanged(), takeUntilDestroyed()).subscribe((term) => {
      this.search = term;
      this.table()?.reset();
    });

    // El género solo aplica a personas naturales.
    this.form.controls.tipo_persona.valueChanges.pipe(takeUntilDestroyed()).subscribe((tipo) => {
      const genero = this.form.controls.genero;
      if (tipo === 'natural') {
        genero.setValidators(Validators.required);
      } else {
        genero.clearValidators();
        genero.setValue(null);
      }
      genero.updateValueAndValidity();
    });
  }

  load(event: TableLazyLoadEvent): void {
    this.lastEvent = event;
    const rows = event.rows ?? this.rows;
    const page = Math.floor((event.first ?? 0) / rows) + 1;
    this.loading.set(true);
    this.service
      .list({ page, per_page: rows, search: this.search, tipo_persona: this.filtroTipo, activo: this.filtroEstado })
      .subscribe({
        next: (res) => {
          this.clientes.set(res.data);
          this.total.set(res.meta.total);
          this.loading.set(false);
        },
        error: () => {
          this.loading.set(false);
          this.toastError('No fue posible cargar los clientes.');
        },
      });
  }

  onSearch(value: string): void {
    this.search$.next(value.trim());
  }

  onFiltro(): void {
    this.table()?.reset();
  }

  buscarMunicipio(event: AutoCompleteCompleteEvent): void {
    this.catalogos.buscarMunicipios(event.query).subscribe((m) => this.municipios.set(m));
  }

  openNew(): void {
    this.editing.set(null);
    this.serverErrors.set({});
    this.form.reset({ tipo_persona: 'natural', activo: true, genero: null, municipio: null });
    this.dialogVisible.set(true);
  }

  openEdit(c: Cliente): void {
    this.editing.set(c);
    this.serverErrors.set({});
    this.form.reset({
      tipo_persona: c.tipo_persona,
      numero_documento: c.numero_documento,
      nombre: c.nombre,
      genero: c.genero,
      municipio: c.municipio,
      telefono: c.telefono ?? '',
      email: c.email ?? '',
      direccion: c.direccion ?? '',
      activo: c.activo,
    });
    this.dialogVisible.set(true);
  }

  save(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const v = this.form.getRawValue();
    const payload: ClientePayload = {
      tipo_persona: v.tipo_persona,
      numero_documento: v.numero_documento.trim(),
      nombre: v.nombre.trim(),
      genero: v.tipo_persona === 'natural' ? v.genero : null,
      municipio_id: v.municipio?.id ?? null,
      telefono: v.telefono || null,
      email: v.email || null,
      direccion: v.direccion || null,
      activo: v.activo,
    };

    const current = this.editing();
    const request = current ? this.service.update(current.id, payload) : this.service.create(payload);
    this.saving.set(true);
    request.subscribe({
      next: () => {
        this.saving.set(false);
        this.dialogVisible.set(false);
        this.messages.add({ severity: 'success', summary: 'Listo', detail: current ? 'Cliente actualizado.' : 'Cliente creado.' });
        this.load(this.lastEvent);
      },
      error: (err: HttpErrorResponse) => {
        this.saving.set(false);
        if (err.status === 422) this.serverErrors.set(err.error?.errors ?? {});
        else this.toastError(err.error?.message ?? 'No fue posible guardar el cliente.');
      },
    });
  }

  toggleEstado(c: Cliente): void {
    const accion = c.activo ? 'inactivar' : 'activar';
    this.confirm.confirm({
      header: 'Confirmar',
      message: `¿Deseas ${accion} al cliente ${c.nombre}?`,
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Sí',
      rejectLabel: 'No',
      rejectButtonStyleClass: 'p-button-text',
      accept: () =>
        this.service.toggleEstado(c.id).subscribe({
          next: () => {
            this.messages.add({ severity: 'success', summary: 'Listo', detail: `Cliente ${c.activo ? 'inactivado' : 'activado'}.` });
            this.load(this.lastEvent);
          },
          error: (err: HttpErrorResponse) => this.toastError(err.error?.message ?? 'No fue posible cambiar el estado.'),
        }),
    });
  }

  remove(c: Cliente): void {
    this.confirm.confirm({
      header: 'Eliminar cliente',
      message: `¿Eliminar a ${c.nombre}? Esta acción no se puede deshacer.`,
      icon: 'pi pi-trash',
      acceptLabel: 'Eliminar',
      rejectLabel: 'Cancelar',
      acceptButtonStyleClass: 'p-button-danger',
      rejectButtonStyleClass: 'p-button-text',
      accept: () =>
        this.service.delete(c.id).subscribe({
          next: () => {
            this.messages.add({ severity: 'success', summary: 'Listo', detail: 'Cliente eliminado.' });
            this.load(this.lastEvent);
          },
          error: (err: HttpErrorResponse) => this.toastError(err.error?.message ?? 'No fue posible eliminar el cliente.'),
        }),
    });
  }

  generoLabel(g: Genero | null): string {
    return g === 'masculino' ? 'Masculino' : g === 'femenino' ? 'Femenino' : '—';
  }

  fieldError(name: string): string | null {
    const ctrl = this.form.get(name);
    if (ctrl && ctrl.touched && ctrl.invalid) {
      if (ctrl.hasError('required')) return 'Este campo es obligatorio.';
      if (ctrl.hasError('email')) return 'Correo no válido.';
      if (ctrl.hasError('pattern')) return 'Solo números, puntos y guiones.';
      if (ctrl.hasError('maxlength')) return 'Texto demasiado largo.';
    }
    const serverKey = name === 'municipio' ? 'municipio_id' : name;
    return this.serverErrors()[serverKey]?.[0] ?? null;
  }

  private toastError(detail: string): void {
    this.messages.add({ severity: 'error', summary: 'Error', detail });
  }
}
