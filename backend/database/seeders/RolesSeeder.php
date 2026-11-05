<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(Rol::BASE) as $slug) {
            Rol::idFor($slug);
        }
    }
}
