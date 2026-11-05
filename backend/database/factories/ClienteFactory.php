<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tipo_persona' => Cliente::NATURAL,
            'numero_documento' => (string) fake()->unique()->numberBetween(10000000, 1999999999),
            'nombre' => fake()->name(),
            'genero' => fake()->randomElement(Cliente::GENEROS),
            'telefono' => '3'.fake()->numerify('#########'),
            'email' => fake()->safeEmail(),
            'activo' => true,
        ];
    }

    public function empresa(): static
    {
        return $this->state(fn () => [
            'tipo_persona' => Cliente::JURIDICA,
            'numero_documento' => fake()->unique()->numerify('9########-#'),
            'nombre' => fake()->company().' S.A.S.',
            'genero' => null,
        ]);
    }
}
