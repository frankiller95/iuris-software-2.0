<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Usuario administrador inicial para desarrollo.
     * Cambia la contraseña al desplegar en producción.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@iuris.test'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'Iuris',
                'rol' => Rol::Admin,
                'activo' => true,
                'password' => 'Admin12345',
            ],
        );
    }
}
