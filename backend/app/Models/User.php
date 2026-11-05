<?php

namespace App\Models;

use App\Enums\Rol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombres',
        'apellidos',
        'numero_documento',
        'email',
        'telefono',
        'rol',
        'activo',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'rol' => 'abogado',
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'rol' => Rol::class,
        ];
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->nombres} {$this->apellidos}"));
    }

    public function isAdmin(): bool
    {
        return $this->rol === Rol::Admin;
    }
}
