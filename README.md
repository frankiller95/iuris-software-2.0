# Iuris Software 2.0

Reconstrucción del sistema de gestión de procesos jurídicos de ZapataDiosa Abogados.

| Capa | Tecnología | Carpeta |
|---|---|---|
| Frontend | Angular 19 + PrimeNG 19 | `frontend/` |
| Backend | Laravel 12 + Sanctum (API REST) | `backend/` |
| Base de datos | MySQL | — |

El análisis del sistema anterior y el plan de módulos está en [docs/MODULOS.md](docs/MODULOS.md).

## Requisitos
- PHP 8.2+, Composer 2
- Node 20+ y npm
- MySQL 8 / MariaDB 10.6+

## Instalación

### Backend
```bash
cd backend
composer install
cp .env.example .env        # ajustar DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed  # crea las tablas y el usuario administrador inicial
php artisan serve --port=8001
```

Usuario inicial (solo desarrollo): `admin@iuris.test` / `Admin12345`. Cambiar la contraseña en producción.

### Frontend
```bash
cd frontend
npm install
npm start                   # http://localhost:4200
```

En desarrollo, Angular redirige `/api` al backend en `http://127.0.0.1:8001` (ver `frontend/proxy.conf.json`).

## Pruebas
```bash
cd backend && php artisan test
```
