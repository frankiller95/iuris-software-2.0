<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Municipio */
class MunicipioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'departamento' => $this->whenLoaded('departamento', fn () => $this->departamento->nombre),
            'etiqueta' => $this->relationLoaded('departamento')
                ? "{$this->nombre} ({$this->departamento->nombre})"
                : $this->nombre,
        ];
    }
}
