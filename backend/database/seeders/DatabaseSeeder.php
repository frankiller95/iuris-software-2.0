<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            DivipolaSeeder::class,
            TiposProcesosSeeder::class,
        ]);

        // Usuario administrador inicial para desarrollo. Cambia la contraseña en producción.
        User::firstOrCreate(
            ['email' => 'admin@iuris.test'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'Iuris',
                'rol_id' => Rol::idFor(Rol::ADMIN),
                'activo' => true,
                'password' => 'Admin12345',
            ],
        );
    }
}
