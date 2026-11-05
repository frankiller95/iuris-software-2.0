<?php

namespace Database\Seeders;

use App\Models\AreaProceso;
use Illuminate\Database\Seeder;

/** Tipos de proceso según docs/TIPOS_PROCESOS.md ("PROCESOS EN IURIS.docx"). */
class TiposProcesosSeeder extends Seeder
{
    public const TIPOS = [
        'Civil' => [
            'Declarativo verbal y declarativo verbal sumario',
            'Ejecutivos',
            'Sucesión',
            'Liquidación de la sociedad conyugal',
            'Liquidación de la sociedad patrimonial',
            'Jurisdicción voluntaria',
            'Responsabilidad civil extracontractual',
            'Monitorio',
            'Divorcio',
            'Declarativo de pertenencia',
        ],
        'Pensiones' => [
            'Indemnización sustitutiva de la pensión de vejez',
            'Devolución de saldos',
            'Invalidez',
            'Sobreviviente',
            'Vejez',
            'Reliquidación de mesada pensional',
            'Corrección de semanas',
            'Indemnización de perjuicios',
            'Reliquidación de la pensión de invalidez',
            'Reliquidación de la pensión de sobreviviente',
        ],
        'Laboral' => [
            'Acoso laboral',
            'Reintegro laboral',
            'Intereses de la liquidación',
            'Intereses a las cesantías',
            'Fuero sindical',
            'Despido sin justa causa',
            'Renuncia con justa causa',
            'Descargos',
            'Pago de prestaciones sociales',
            'Despido colectivo',
        ],
        'Familia' => [
            'Unión marital de hecho',
            'Patria potestad',
            'Corrección de registros civiles',
            'Testamentos',
            'Capitulaciones matrimoniales',
            'Fijación de cuota alimentaria',
        ],
        'Penal' => [
            'Representación de víctimas',
            'Audiencia preliminar',
            'Ejecución de penas',
            'Legalización de captura',
            'Concierto para delinquir',
            'Homicidio',
            'Hurto',
        ],
    ];

    public function run(): void
    {
        foreach (self::TIPOS as $area => $tipos) {
            $areaModel = AreaProceso::firstOrCreate(['nombre' => $area]);
            foreach ($tipos as $tipo) {
                $areaModel->tipos()->firstOrCreate(['nombre' => $tipo]);
            }
        }
    }
}
