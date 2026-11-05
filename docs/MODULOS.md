# Iuris Software 2.0 — Análisis del sistema anterior y módulos a construir

> Documento base del proyecto. Resume la revisión del sistema en producción
> (`iurisSoftware`, Laravel 10 + Blade + plantilla Argon) y define los módulos
> que tendrá la versión 2.0 (Angular 19 + PrimeNG 19 / Laravel 12 + Sanctum / MySQL).

---

## 1. Resumen del sistema anterior

| Aspecto | Versión 1 (en uso) |
|---|---|
| Framework | Laravel 10, vistas Blade, jQuery, Select2, DataTables, SweetAlert, Dropzone |
| Plantilla | Argon Dashboard (laravel-frontend-presets/argon) |
| Autenticación | Sesión web de Laravel (`Auth::attempt`), registro abierto a invitados |
| Roles | No existen. Todo usuario autenticado ve y edita todo |
| Base de datos | MySQL. Solo la tabla `users` tiene migración; el resto de tablas se crearon a mano |
| Menú | Inicio · Clientes (crear / listar) · Procesos (crear / listar) |

### Pantallas vistas en los screenshots

1. **Dashboard**: banner "Bienvenido, abogad@ {nombre}". Sin indicadores.
2. **Listado de procesos**: tabla con Código, Cliente, Demandado, Abogado, Etapa y acciones (ver/editar, documentos, inactivar). Búsqueda y paginación con DataTables.
3. **Login** y menú lateral con Clientes / Procesos.

---

## 2. Modelo de datos encontrado (v1)

| Tabla | Campos principales | Notas |
|---|---|---|
| `users` | username, firstname, lastname, email, password, address, city, country, postal, about | Campos heredados de la plantilla Argon. Sin rol ni estado |
| `clientes` | nombre1, nombre2, apellido1, apellido2, numero_cedula, email, celular, direccion, id_municipio, estado, fecha_registro | Inactivar = `estado = 3` |
| `demandados` | id_tipo_persona, cedula_nit, id_municipio, nombre_demandado, estado, fecha_registro | Se crea desde un modal dentro del formulario de procesos |
| `procesos` | codigo_proceso, id_cliente, id_demandado, id_etapa_proceso, id_tipo_tematica, id_tipo_honorario, valor_honorario, estado, usuario_responsable, fecha_registro, fecha_actualizacion | Inactivar = `estado = 0`. `usuario_responsable` = abogado |
| `rel_procesos_docs` | id_proceso, id_usuario_carga, nombre_archivo, ruta_archivo, estado, fecha_registro | Archivos guardados en `public/documents/procesos/{id}` |
| `rel_procesos_observaciones` | id_proceso, texto_observacion, id_usuario_observador, estado, fecha_registro | Historial de observaciones |
| `etapas_procesos` | nombre_proceso, estado | Catálogo (ej. "Proceso Activo") |
| `subtematicas` | nombre_subtematica, id_tematica, estado | Catálogo. Referencia una tabla `tematicas` sin modelo |
| `tipos_honorarios` | tipo_honorario, estado | Catálogo |
| `tipos_personas` | tipo_persona, estado | Catálogo (natural / jurídica) |
| `departamentos` / `municipios` | departamento / municipio, id_departamento | Catálogo geográfico (PK `id_departamento`, `id_municipio`) |

---

## 3. Hallazgos de la revisión

### Seguridad (prioridad alta)
- **El archivo `.env` está versionado en el repositorio `iurisSoftware`** con el host, usuario y contraseña de la base de datos de producción. Se recomienda **cambiar la contraseña de la BD** y sacar el `.env` del repositorio (`git rm --cached .env`). Borrarlo del último commit no lo elimina del historial.
- El registro de usuarios (`/register`) está abierto a cualquier visitante.
- No hay roles: cualquier usuario puede ver e inactivar procesos de otros abogados.
- La carga de archivos no valida tipo ni tamaño, y los archivos quedan en `public/` (accesibles por URL directa sin autenticación).
- Rutas sobrantes de la plantilla (`/virtual-reality`, `/rtl`, `/{page}`, billing, etc.).

### Datos y lógica
- Estados inconsistentes: clientes inactivos usan `3`, procesos inactivos usan `0`.
- El código de proceso se calcula con "último + 1" sin bloqueo: dos creaciones simultáneas pueden chocar.
- Las tablas no tienen migraciones, por lo que no se pueden recrear en otro entorno.
- Fechas manejadas a mano (`fecha_registro`) en lugar de `created_at/updated_at`.

---

## 4. Arquitectura de la versión 2.0

```
iuris-software-v2/
├── backend/    Laravel 12 — API REST JSON, autenticación con Sanctum (tokens Bearer)
├── frontend/   Angular 19 (standalone) + PrimeNG 19 (tema Aura, color primario ámbar)
└── docs/       Documentación funcional y técnica
```

- **API**: todas las rutas bajo `/api`, respuestas JSON, validación con Form Requests, recursos con API Resources.
- **Autenticación**: Sanctum con tokens personales. El frontend guarda el token y lo envía en `Authorization: Bearer`. Usuarios inactivos no pueden iniciar sesión. Login limitado a 5 intentos por minuto.
- **Roles** (tabla `roles`): `admin` / Administrador (gestiona usuarios y catálogos, ve todos los procesos), `abogado` / Abogado (gestiona clientes y sus procesos) y `pasante` / Pasante (apoya la gestión de clientes y procesos, sin permisos para inactivar ni eliminar).
- **Migraciones para todas las tablas**, con llaves foráneas, `timestamps` y borrado lógico (`activo` / `softDeletes`).
- **Archivos**: disco privado de Laravel, descarga solo a través de la API autenticada.

---

## 5. Módulos a construir

| # | Módulo | Descripción | Rol | Sprint |
|---|---|---|---|---|
| 1 | **Autenticación** | Login, logout, usuario actual, guard de rutas en Angular, interceptor de token | Todos | **1** ✅ |
| 2 | **Usuarios** | CRUD de usuarios (abogados y administradores), activar/inactivar, cambio de contraseña | Admin | **1** ✅ |
| 3 | Layout / Dashboard | Menú lateral, barra superior, bienvenida. Más adelante: indicadores (procesos por etapa, por abogado) | Todos | 1 (base) / 4 |
| 4 | Catálogos | Departamentos y municipios (DIVIPOLA) ✅, áreas y tipos de proceso ✅. Pendientes: etapas, tipos de honorario | Admin | 2 |
| 5 | Clientes | Persona natural o empresa, cédula/NIT único, nombre o razón social, género, ciudad. Listar, buscar, filtrar, crear, editar, inactivar | Todos | **2** ✅ |
| 6 | Demandados | Crear (también desde el formulario de procesos), editar, listar, inactivar | Todos | 2 |
| 7 | Procesos | Crear, editar, listar con filtros (abogado, etapa, cliente), inactivar. Código consecutivo seguro | Todos (abogado: solo los suyos) | 3 |
| 8 | Documentos del proceso | Subir, listar, descargar y eliminar archivos (validación de tipo y tamaño) | Todos | 3 |
| 9 | Observaciones del proceso | Historial con autor y fecha, agregar observación | Todos | 3 |
| 10 | Mi perfil | Ver/editar datos propios y cambiar contraseña | Todos | 4 |
| 11 | Recuperar contraseña | Envío de enlace por correo | Todos | 4 |
| 12 | Migración de datos | Script para pasar los datos de v1 a v2 (mapeo de tablas y estados) | — | 4 |

---

## 6. Sprint 1 — Alcance entregado

### Backend (`backend/`)
- Laravel 12 + Sanctum (`php artisan install:api`).
- Tabla `users` adaptada: `nombres`, `apellidos`, `numero_documento`, `email`, `telefono`, `rol` (`admin`/`abogado`), `activo`, `password`.
- Seeder con un usuario administrador inicial (credenciales de desarrollo en `backend/database/seeders/DatabaseSeeder.php`; cambiarlas en producción).
- Endpoints:

| Método | Ruta | Descripción | Acceso |
|---|---|---|---|
| POST | `/api/auth/login` | Devuelve token y usuario | Público (5 intentos/min) |
| GET | `/api/auth/me` | Usuario autenticado | Autenticado |
| POST | `/api/auth/logout` | Revoca el token actual | Autenticado |
| GET | `/api/usuarios` | Listado paginado con búsqueda (`?search=&page=&per_page=`) | Admin |
| POST | `/api/usuarios` | Crear usuario | Admin |
| GET | `/api/usuarios/{id}` | Ver usuario | Admin |
| PUT | `/api/usuarios/{id}` | Actualizar (contraseña opcional) | Admin |
| PATCH | `/api/usuarios/{id}/estado` | Activar / inactivar | Admin |
| DELETE | `/api/usuarios/{id}` | Eliminar | Admin |

- Pruebas automáticas (`php artisan test`) para login y gestión de usuarios.

### Frontend (`frontend/`)
- Angular 19 standalone + PrimeNG 19 (tema Aura con color ámbar, similar al amarillo de v1).
- Pantalla de login, layout con menú lateral (Inicio, Usuarios; Clientes y Procesos visibles como "próximamente").
- Interceptor que agrega el token y cierra sesión ante un 401; guards de autenticación y de rol admin.
- Módulo de usuarios: tabla con búsqueda y paginación desde el servidor, formulario en diálogo para crear/editar, activar/inactivar y eliminar con confirmación.

### Mapeo de usuarios v1 → v2 (para la migración futura)
| v1 | v2 |
|---|---|
| firstname | nombres |
| lastname | apellidos |
| email | email |
| password (bcrypt) | password (se conserva el hash) |
| — | rol = `abogado` (asignar admin manualmente) |
| — | activo = `true` |
| username, address, city, country, postal, about | No se migran |

---

## 7. Sprint 2 — Alcance entregado

### Base de datos (migraciones)
| Tabla | Descripción |
|---|---|
| `roles` | Administrador, Abogado, Pasante (`users.rol_id` → `roles.id`) |
| `departamentos` / `municipios` | Catálogo DIVIPOLA de Colombia: 33 departamentos y 1122 municipios (`backend/database/data/divipola.json`, fuente datos.gov.co) |
| `clientes` | tipo_persona (natural / juridica), numero_documento (cédula o NIT, único), nombre (nombre o razón social), genero (masculino / femenino, solo persona natural), municipio_id (ciudad), telefono, email, direccion (opcionales), activo, created_by |
| `areas_proceso` / `tipos_proceso` | 5 áreas y 43 tipos de proceso tomados de `docs/PROCESOS EN IURIS.docx` (ver [TIPOS_PROCESOS.md](TIPOS_PROCESOS.md)) |

La tabla `procesos` se crea en el siguiente sprint, con `cliente_id` y `tipo_proceso_id`.

### Permisos sobre clientes
| Acción | Admin | Abogado | Pasante |
|---|---|---|---|
| Ver, crear, editar | ✅ | ✅ | ✅ |
| Activar / inactivar | ✅ | ✅ | — |
| Eliminar | ✅ | — | — |

### Nuevos endpoints
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/clientes` | Listado paginado (`?search=&tipo_persona=&activo=&page=&per_page=`) |
| POST / PUT | `/api/clientes`, `/api/clientes/{id}` | Crear / actualizar |
| PATCH | `/api/clientes/{id}/estado` | Activar / inactivar |
| DELETE | `/api/clientes/{id}` | Eliminar (admin) |
| GET | `/api/municipios?search=` | Autocompletado de ciudad (máx. 50) |
| GET | `/api/departamentos` | Departamentos |
| GET | `/api/areas-proceso` | Áreas con sus tipos de proceso |
| GET | `/api/roles` | Roles (admin) |

### Nota sobre las pruebas
Las pruebas usan SQLite en memoria. Si la configuración está en caché (`php artisan config:cache`), Laravel ignora `phpunit.xml`
y las pruebas borrarían la base MySQL; `tests/TestCase.php` ahora detiene las pruebas en ese caso.
Ejecuta `php artisan config:clear` antes de `php artisan test`.
