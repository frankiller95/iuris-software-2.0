<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MunicipioResource;
use App\Models\AreaProceso;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Listas de solo lectura para selects y autocompletados. */
class CatalogoController extends Controller
{
    public function roles(): JsonResponse
    {
        return response()->json([
            'data' => Rol::orderBy('id')->get(['id', 'slug', 'nombre', 'descripcion']),
        ]);
    }

    public function departamentos(): JsonResponse
    {
        return response()->json(['data' => Departamento::orderBy('nombre')->get(['id', 'codigo', 'nombre'])]);
    }

    /** Búsqueda de municipios por nombre (?search=) y/o departamento (?departamento_id=). Máximo 50 resultados. */
    public function municipios(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->input('search', ''));

        $municipios = Municipio::query()
            ->with('departamento')
            ->when($search !== '', fn ($q) => $q->where('nombre', 'like', "%{$search}%"))
            ->when($request->filled('departamento_id'), fn ($q) => $q->where('departamento_id', $request->integer('departamento_id')))
            // Primero coincidencia exacta o palabra final ("Santiago de Cali" para "cali"), luego los que empiezan igual.
            ->when($search !== '', fn ($q) => $q->orderByRaw(
                'CASE WHEN nombre = ? OR nombre LIKE ? THEN 0 WHEN nombre LIKE ? THEN 1 ELSE 2 END',
                [$search, "% {$search}", "{$search}%"],
            ))
            ->orderBy('nombre')
            ->limit(50)
            ->get();

        return MunicipioResource::collection($municipios);
    }

    public function areasProceso(): JsonResponse
    {
        $areas = AreaProceso::where('activo', true)
            ->with(['tipos' => fn ($q) => $q->where('activo', true)->orderBy('nombre')])
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json([
            'data' => $areas->map(fn ($a) => [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'tipos' => $a->tipos->map(fn ($t) => ['id' => $t->id, 'nombre' => $t->nombre])->values(),
            ]),
        ]);
    }
}
