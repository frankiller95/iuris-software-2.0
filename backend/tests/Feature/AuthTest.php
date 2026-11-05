<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_con_credenciales_validas_devuelve_token(): void
    {
        $user = User::factory()->create(['email' => 'abogado@iuris.test']);

        $this->postJson('/api/auth/login', ['email' => 'abogado@iuris.test', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'nombre_completo', 'rol', 'activo']])
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_login_con_contrasena_incorrecta_falla(): void
    {
        User::factory()->create(['email' => 'abogado@iuris.test']);

        $this->postJson('/api/auth/login', ['email' => 'abogado@iuris.test', 'password' => 'otra'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        User::factory()->inactivo()->create(['email' => 'inactivo@iuris.test']);

        $this->postJson('/api/auth/login', ['email' => 'inactivo@iuris.test', 'password' => 'password'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_me_requiere_autenticacion(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_me_y_logout_con_token(): void
    {
        User::factory()->create(['email' => 'abogado@iuris.test']);
        $token = $this->postJson('/api/auth/login', ['email' => 'abogado@iuris.test', 'password' => 'password'])
            ->json('token');

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'abogado@iuris.test');

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_tiene_limite_de_intentos(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['email' => 'x@iuris.test', 'password' => 'x'])->assertStatus(422);
        }

        $this->postJson('/api/auth/login', ['email' => 'x@iuris.test', 'password' => 'x'])->assertStatus(429);
    }
}
