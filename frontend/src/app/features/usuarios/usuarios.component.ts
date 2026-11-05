import { Component, inject, signal, viewChild } from '@angular/core';
import { AbstractControl, FormBuilder, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
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
import { PasswordModule } from 'primeng/password';
import { ToggleSwitchModule } from 'primeng/toggleswitch';
import { TagModule } from 'primeng/tag';
import { TooltipModule } from 'primeng/tooltip';
import { ConfirmationService, MessageService } from 'primeng/api';
import { Rol, User, UserPayload } from '../../core/models/user.model';
import { AuthService } from '../../core/services/auth.service';
import { CatalogosService } from '../../core/services/catalogos.service';
import { UsuariosService } from './usuarios.service';

function passwordsMatch(group: AbstractControl): ValidationErrors | null {
  const p = group.get('password')?.value;
  const c = group.get('password_confirmation')?.value;
  return p && p !== c ? { mismatch: true } : null;
}

@Component({
  selector: 'app-usuarios',
  imports: [
    ReactiveFormsModule, TableModule, ButtonModule, InputTextModule, IconFieldModule, InputIconModule,
    DialogModule, SelectModule, PasswordModule, ToggleSwitchModule, TagModule, TooltipModule,
  ],
  templateUrl: './usuarios.component.html',
  styleUrl: './usuarios.component.scss',
})
export class UsuariosComponent {
  private service = inject(UsuariosService);
  private messages = inject(MessageService);
  private confirm = inject(ConfirmationService);
  private fb = inject(FormBuilder);
  auth = inject(AuthService);

  table = viewChild<Table>('dt');

  usuarios = signal<User[]>([]);
  total = signal(0);
  loading = signal(false);
  rows = 10;
  private search = '';
  private lastEvent: TableLazyLoadEvent = { first: 0, rows: this.rows };
  private search$ = new Subject<string>();

  dialogVisible = signal(false);
  saving = signal(false);
  editing = signal<User | null>(null);
  serverErrors = signal<Record<string, string[]>>({});

  roles = toSignal(inject(CatalogosService).roles(), { initialValue: [] as Rol[] });

  form = this.fb.nonNullable.group(
    {
      nombres: ['', [Validators.required, Validators.maxLength(100)]],
      apellidos: ['', [Validators.required, Validators.maxLength(100)]],
      numero_documento: [''],
      email: ['', [Validators.required, Validators.email]],
      telefono: [''],
      rol_id: [null as number | null, Validators.required],
      activo: [true],
      password: [''],
      password_confirmation: [''],
    },
    { validators: passwordsMatch },
  );

  constructor() {
    this.search$
      .pipe(debounceTime(400), distinctUntilChanged(), takeUntilDestroyed())
      .subscribe((term) => {
        this.search = term;
        this.table()?.reset(); // vuelve a la página 1 y dispara onLazyLoad
      });
  }

  load(event: TableLazyLoadEvent): void {
    this.lastEvent = event;
    const rows = event.rows ?? this.rows;
    const page = Math.floor((event.first ?? 0) / rows) + 1;
    this.loading.set(true);
    this.service.list({ page, per_page: rows, search: this.search }).subscribe({
      next: (res) => {
        this.usuarios.set(res.data);
        this.total.set(res.meta.total);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.toastError('No fue posible cargar los usuarios.');
      },
    });
  }

  onSearch(value: string): void {
    this.search$.next(value.trim());
  }

  openNew(): void {
    this.editing.set(null);
    this.serverErrors.set({});
    this.form.reset({ rol_id: this.roles().find((r) => r.slug === 'abogado')?.id ?? null, activo: true });
    this.setPasswordRequired(true);
    this.dialogVisible.set(true);
  }

  openEdit(user: User): void {
    this.editing.set(user);
    this.serverErrors.set({});
    this.form.reset({
      nombres: user.nombres,
      apellidos: user.apellidos,
      numero_documento: user.numero_documento ?? '',
      email: user.email,
      telefono: user.telefono ?? '',
      rol_id: user.rol_id,
      activo: user.activo,
      password: '',
      password_confirmation: '',
    });
    this.setPasswordRequired(false);
    this.dialogVisible.set(true);
  }

  private setPasswordRequired(required: boolean): void {
    const ctrl = this.form.controls.password;
    ctrl.setValidators(required ? [Validators.required, Validators.minLength(8)] : [Validators.minLength(8)]);
    ctrl.updateValueAndValidity();
  }

  save(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const raw = this.form.getRawValue();
    const payload: UserPayload = {
      ...raw,
      numero_documento: raw.numero_documento || null,
      telefono: raw.telefono || null,
    };
    if (!payload.password) {
      delete payload.password;
      delete payload.password_confirmation;
    }

    const current = this.editing();
    const request = current ? this.service.update(current.id, payload) : this.service.create(payload);
    this.saving.set(true);
    request.subscribe({
      next: () => {
        this.saving.set(false);
        this.dialogVisible.set(false);
        this.messages.add({ severity: 'success', summary: 'Listo', detail: current ? 'Usuario actualizado.' : 'Usuario creado.' });
        this.load(this.lastEvent);
      },
      error: (err: HttpErrorResponse) => {
        this.saving.set(false);
        if (err.status === 422) this.serverErrors.set(err.error?.errors ?? {});
        else this.toastError(err.error?.message ?? 'No fue posible guardar el usuario.');
      },
    });
  }

  toggleEstado(user: User): void {
    const accion = user.activo ? 'inactivar' : 'activar';
    this.confirm.confirm({
      header: 'Confirmar',
      message: `¿Deseas ${accion} a ${user.nombre_completo}?`,
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Sí',
      rejectLabel: 'No',
      rejectButtonStyleClass: 'p-button-text',
      accept: () =>
        this.service.toggleEstado(user.id).subscribe({
          next: () => {
            this.messages.add({ severity: 'success', summary: 'Listo', detail: `Usuario ${user.activo ? 'inactivado' : 'activado'}.` });
            this.load(this.lastEvent);
          },
          error: (err: HttpErrorResponse) => this.toastError(err.error?.message ?? 'No fue posible cambiar el estado.'),
        }),
    });
  }

  remove(user: User): void {
    this.confirm.confirm({
      header: 'Eliminar usuario',
      message: `¿Eliminar a ${user.nombre_completo}? Esta acción no se puede deshacer.`,
      icon: 'pi pi-trash',
      acceptLabel: 'Eliminar',
      rejectLabel: 'Cancelar',
      acceptButtonStyleClass: 'p-button-danger',
      rejectButtonStyleClass: 'p-button-text',
      accept: () =>
        this.service.delete(user.id).subscribe({
          next: () => {
            this.messages.add({ severity: 'success', summary: 'Listo', detail: 'Usuario eliminado.' });
            this.load(this.lastEvent);
          },
          error: (err: HttpErrorResponse) => this.toastError(err.error?.message ?? 'No fue posible eliminar el usuario.'),
        }),
    });
  }

  isSelf(user: User): boolean {
    return this.auth.user()?.id === user.id;
  }

  fieldError(name: string): string | null {
    const ctrl = this.form.get(name);
    if (ctrl && ctrl.touched && ctrl.invalid) {
      if (ctrl.hasError('required')) return 'Este campo es obligatorio.';
      if (ctrl.hasError('email')) return 'Correo no válido.';
      if (ctrl.hasError('minlength')) return 'Mínimo 8 caracteres.';
      if (ctrl.hasError('maxlength')) return 'Texto demasiado largo.';
    }
    if (name === 'password_confirmation' && this.form.hasError('mismatch') && ctrl?.touched) {
      return 'Las contraseñas no coinciden.';
    }
    return this.serverErrors()[name]?.[0] ?? null;
  }

  private toastError(detail: string): void {
    this.messages.add({ severity: 'error', summary: 'Error', detail });
  }
}
