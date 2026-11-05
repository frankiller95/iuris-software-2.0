<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    public const ADMIN = 'admin';
    public const ABOGADO = 'abogado';
    public const PASANTE = 'pasante';

    /** Roles base del sistema: slug => [nombre, descripción]. */
    public const BASE = [
        self::ADMIN => ['Administrador', 'Gestiona usuarios, catálogos y todos los procesos.'],
        self::ABOGADO => ['Abogado', 'Gestiona clientes y sus procesos.'],
        self::PASANTE => ['Pasante', 'Apoya la gestión de clientes y procesos.'],
    ];

    protected $fillable = ['slug', 'nombre', 'descripcion'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    /** Id del rol por slug; lo crea si aún no existe (útil en pruebas y seeders). */
    public static function idFor(string $slug): int
    {
        [$nombre, $descripcion] = self::BASE[$slug];

        return static::firstOrCreate(['slug' => $slug], compact('nombre', 'descripcion'))->id;
    }
}
