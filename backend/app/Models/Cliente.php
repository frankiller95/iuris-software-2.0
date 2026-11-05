<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    public const NATURAL = 'natural';
    public const JURIDICA = 'juridica';

    public const TIPOS_PERSONA = [self::NATURAL, self::JURIDICA];
    public const GENEROS = ['masculino', 'femenino'];

    protected $fillable = [
        'tipo_persona',
        'numero_documento',
        'nombre',
        'genero',
        'municipio_id',
        'telefono',
        'email',
        'direccion',
        'activo',
        'created_by',
    ];

    protected $attributes = [
        'activo' => true,
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
