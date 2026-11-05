<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\ClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Permisos: todos los roles ven, crean y editan clientes;
 * activar/inactivar: admin y abogado; eliminar: solo admin.
 */
class ClienteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
        $search = trim((string) $request->input('search', ''));

        $clientes = Cliente::query()
            ->with('municipio.departamento')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('numero_documento', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('tipo_persona'), fn ($q) => $q->where('tipo_persona', $request->tipo_persona))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('nombre')
            ->paginate($perPage);

        return ClienteResource::collection($clientes);
    }

    public function store(ClienteRequest $request): JsonResponse
    {
        $cliente = Cliente::create($request->validated() + ['created_by' => $request->user()->id]);

        return (new ClienteResource($cliente->load('municipio.departamento')))->response()->setStatusCode(201);
    }

    public function show(Cliente $cliente): ClienteResource
    {
        return new ClienteResource($cliente->load('municipio.departamento'));
    }

    public function update(ClienteRequest $request, Cliente $cliente): ClienteResource
    {
        $data = $request->validated();

        // Solo admin y abogado pueden cambiar el estado.
        if (! $request->user()->hasRol(Rol::ADMIN, Rol::ABOGADO)) {
            unset($data['activo']);
        }

        $cliente->update($data);

        return new ClienteResource($cliente->load('municipio.departamento'));
    }

    public function toggleEstado(Request $request, Cliente $cliente): ClienteResource|JsonResponse
    {
        if (! $request->user()->hasRol(Rol::ADMIN, Rol::ABOGADO)) {
            return response()->json(['message' => 'No tienes permisos para cambiar el estado del cliente.'], 403);
        }

        $cliente->update(['activo' => ! $cliente->activo]);

        return new ClienteResource($cliente->load('municipio.departamento'));
    }

    public function destroy(Request $request, Cliente $cliente): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Solo un administrador puede eliminar clientes.'], 403);
        }

        $cliente->delete();

        return response()->json(null, 204);
    }
}
