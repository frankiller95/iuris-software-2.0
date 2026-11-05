<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoProceso extends Model
{
    protected $table = 'tipos_proceso';

    protected $fillable = ['area_proceso_id', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(AreaProceso::class, 'area_proceso_id');
    }
}
