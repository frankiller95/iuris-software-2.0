<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'numero_documento' => (string) fake()->unique()->numberBetween(10000000, 1999999999),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => '3'.fake()->numerify('#########'),
            'rol_id' => fn () => Rol::idFor(Rol::ABOGADO),
            'activo' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['rol_id' => Rol::idFor(Rol::ADMIN)]);
    }

    public function pasante(): static
    {
        return $this->state(fn () => ['rol_id' => Rol::idFor(Rol::PASANTE)]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
