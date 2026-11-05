<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AreaProceso extends Model
{
    protected $table = 'areas_proceso';

    protected $fillable = ['nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function tipos(): HasMany
    {
        return $this->hasMany(TipoProceso::class, 'area_proceso_id');
    }
}
