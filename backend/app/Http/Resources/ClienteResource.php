<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Cliente */
class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_persona' => $this->tipo_persona,
            'numero_documento' => $this->numero_documento,
            'nombre' => $this->nombre,
            'genero' => $this->genero,
            'municipio_id' => $this->municipio_id,
            'municipio' => $this->whenLoaded('municipio', fn () => $this->municipio ? new MunicipioResource($this->municipio) : null),
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'activo' => $this->activo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
