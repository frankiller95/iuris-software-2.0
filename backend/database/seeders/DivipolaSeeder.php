<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Departamentos y municipios de Colombia (DIVIPOLA, datos.gov.co) desde database/data/divipola.json. */
class DivipolaSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('municipios')->exists()) {
            return;
        }

        $data = json_decode(file_get_contents(database_path('data/divipola.json')), true);
        $now = now();

        DB::table('departamentos')->insert(array_map(fn ($d) => [
            'codigo' => $d['codigo'],
            'nombre' => $d['nombre'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $data['departamentos']));

        $ids = DB::table('departamentos')->pluck('id', 'codigo');

        $municipios = array_map(fn ($m) => [
            'departamento_id' => $ids[$m['departamento']],
            'codigo' => $m['codigo'],
            'nombre' => $m['nombre'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $data['municipios']);

        foreach (array_chunk($municipios, 500) as $chunk) {
            DB::table('municipios')->insert($chunk);
        }
    }
}
