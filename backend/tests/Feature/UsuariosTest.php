<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function datos(array $extra = []): array
    {
        return array_merge([
            'nombres' => 'Miguel Angel',
            'apellidos' => 'Castro Arboleda',
            'numero_documento' => '1234567890',
            'email' => 'miguel@iuris.test',
            'telefono' => '3001234567',
            'rol' => 'abogado',
            'activo' => true,
            'password' => 'Secreto123',
            'password_confirmation' => 'Secreto123',
        ], $extra);
    }

    public function test_abogado_no_puede_gestionar_usuarios(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/usuarios')->assertForbidden();
        $this->postJson('/api/usuarios', $this->datos())->assertForbidden();
    }

    public function test_admin_lista_usuarios_paginados_y_busca(): void
    {
        User::factory()->count(12)->create();
        User::factory()->create(['nombres' => 'Lina Marcela']);
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/usuarios?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 14);

        $this->getJson('/api/usuarios?search=Lina')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombres', 'Lina Marcela');
    }

    public function test_admin_crea_usuario(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/usuarios', $this->datos())
            ->assertCreated()
            ->assertJsonPath('data.nombre_completo', 'Miguel Angel Castro Arboleda');

        $user = User::where('email', 'miguel@iuris.test')->first();
        $this->assertTrue(Hash::check('Secreto123', $user->password));
    }

    public function test_crear_valida_campos_y_unicos(): void
    {
        User::factory()->create(['email' => 'miguel@iuris.test']);
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/usuarios', $this->datos(['nombres' => '', 'rol' => 'otro', 'password_confirmation' => 'x']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombres', 'email', 'rol', 'password']);
    }

    public function test_admin_actualiza_sin_cambiar_contrasena(): void
    {
        $user = User::factory()->create();
        $hashAnterior = $user->password;
        Sanctum::actingAs($this->admin);

        $this->putJson("/api/usuarios/{$user->id}", $this->datos(['email' => $user->email, 'password' => null, 'password_confirmation' => null]))
            ->assertOk()
            ->assertJsonPath('data.nombres', 'Miguel Angel');

        $this->assertSame($hashAnterior, $user->fresh()->password);
    }

    public function test_inactivar_usuario_revoca_sus_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('spa');
        Sanctum::actingAs($this->admin);

        $this->patchJson("/api/usuarios/{$user->id}/estado")
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->assertCount(0, $user->tokens()->get());
    }

    public function test_admin_no_puede_inactivarse_ni_eliminarse(): void
    {
        Sanctum::actingAs($this->admin);

        $this->patchJson("/api/usuarios/{$this->admin->id}/estado")->assertStatus(422);
        $this->deleteJson("/api/usuarios/{$this->admin->id}")->assertStatus(422);
        $this->putJson("/api/usuarios/{$this->admin->id}", $this->datos(['email' => $this->admin->email, 'password' => null, 'password_confirmation' => null]))
            ->assertStatus(422);
    }

    public function test_admin_elimina_usuario(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($this->admin);

        $this->deleteJson("/api/usuarios/{$user->id}")->assertNoContent();
        $this->assertModelMissing($user);
    }
}
