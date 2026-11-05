<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\UsuarioRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UsuarioController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->input('per_page', 10), 1), 100);
        $search = trim((string) $request->input('search', ''));

        $usuarios = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('numero_documento', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->paginate($perPage);

        return UserResource::collection($usuarios);
    }

    public function store(UsuarioRequest $request): JsonResponse
    {
        $usuario = User::create($request->validated());

        return (new UserResource($usuario))->response()->setStatusCode(201);
    }

    public function show(User $usuario): UserResource
    {
        return new UserResource($usuario);
    }

    public function update(UsuarioRequest $request, User $usuario): UserResource|JsonResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Un admin no puede quitarse su propio rol ni inactivarse.
        if ($usuario->is($request->user()) && ((int) $data['rol_id'] !== $usuario->rol_id || ($data['activo'] ?? true) == false)) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol ni inactivarte.'], 422);
        }

        $usuario->update($data);

        if (! $usuario->activo) {
            $usuario->tokens()->delete(); // cierra sus sesiones abiertas
        }

        return new UserResource($usuario);
    }

    public function toggleEstado(Request $request, User $usuario): UserResource|JsonResponse
    {
        if ($usuario->is($request->user())) {
            return response()->json(['message' => 'No puedes inactivar tu propio usuario.'], 422);
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        if (! $usuario->activo) {
            $usuario->tokens()->delete();
        }

        return new UserResource($usuario);
    }

    public function destroy(Request $request, User $usuario): JsonResponse
    {
        if ($usuario->is($request->user())) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        $usuario->tokens()->delete();
        $usuario->delete();

        return response()->json(null, 204);
    }
}
