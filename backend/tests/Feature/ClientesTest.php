<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\User;
use Database\Seeders\TiposProcesosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientesTest extends TestCase
{
    use RefreshDatabase;

    private Municipio $cali;

    protected function setUp(): void
    {
        parent::setUp();
        $valle = Departamento::create(['codigo' => '76', 'nombre' => 'Valle del Cauca']);
        $this->cali = $valle->municipios()->create(['codigo' => '76001', 'nombre' => 'Cali']);
    }

    private function natural(array $extra = []): array
    {
        return array_merge([
            'tipo_persona' => 'natural',
            'numero_documento' => '1144000111',
            'nombre' => 'Lina Marcela Mosquera Olaya',
            'genero' => 'femenino',
            'municipio_id' => $this->cali->id,
            'telefono' => '3001234567',
        ], $extra);
    }

    public function test_requiere_autenticacion(): void
    {
        $this->getJson('/api/clientes')->assertUnauthorized();
    }

    public function test_crea_persona_natural(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/clientes', $this->natural())
            ->assertCreated()
            ->assertJsonPath('data.nombre', 'Lina Marcela Mosquera Olaya')
            ->assertJsonPath('data.municipio.etiqueta', 'Cali (Valle del Cauca)');

        $this->assertDatabaseHas('clientes', ['numero_documento' => '1144000111', 'created_by' => $user->id]);
    }

    public function test_empresa_no_guarda_genero(): void
    {
        Sanctum::actingAs(User::factory()->pasante()->create());

        $this->postJson('/api/clientes', $this->natural([
            'tipo_persona' => 'juridica',
            'numero_documento' => '900123456-7',
            'nombre' => 'Colpensiones',
            'genero' => 'masculino',
        ]))->assertCreated()->assertJsonPath('data.genero', null);
    }

    public function test_valida_genero_documento_y_ciudad(): void
    {
        Cliente::factory()->create(['numero_documento' => '1144000111']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/clientes', $this->natural(['genero' => null, 'municipio_id' => 99999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['genero', 'numero_documento', 'municipio_id']);

        $this->postJson('/api/clientes', $this->natural(['numero_documento' => 'abc', 'tipo_persona' => 'otro']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['numero_documento', 'tipo_persona']);
    }

    public function test_lista_busca_y_filtra(): void
    {
        Cliente::factory()->count(3)->create();
        Cliente::factory()->empresa()->create(['nombre' => 'ARL Sura']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/clientes')->assertOk()->assertJsonPath('meta.total', 4);
        $this->getJson('/api/clientes?search=sura')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/clientes?tipo_persona=juridica')->assertOk()->assertJsonPath('data.0.nombre', 'ARL Sura');
    }

    public function test_actualiza_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/clientes/{$cliente->id}", $this->natural(['numero_documento' => $cliente->numero_documento]))
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Lina Marcela Mosquera Olaya');
    }

    public function test_pasante_no_cambia_estado_ni_elimina(): void
    {
        $cliente = Cliente::factory()->create();
        Sanctum::actingAs(User::factory()->pasante()->create());

        $this->patchJson("/api/clientes/{$cliente->id}/estado")->assertForbidden();
        $this->deleteJson("/api/clientes/{$cliente->id}")->assertForbidden();
    }

    public function test_abogado_inactiva_y_admin_elimina(): void
    {
        $cliente = Cliente::factory()->create();

        Sanctum::actingAs(User::factory()->create());
        $this->patchJson("/api/clientes/{$cliente->id}/estado")->assertOk()->assertJsonPath('data.activo', false);
        $this->deleteJson("/api/clientes/{$cliente->id}")->assertForbidden();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->deleteJson("/api/clientes/{$cliente->id}")->assertNoContent();
        $this->assertModelMissing($cliente);
    }

    public function test_catalogos(): void
    {
        $this->seed(TiposProcesosSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/municipios?search=cal')->assertOk()->assertJsonPath('data.0.etiqueta', 'Cali (Valle del Cauca)');

        $areas = $this->getJson('/api/areas-proceso')->assertOk()->json('data');
        $this->assertCount(5, $areas);
        $this->assertSame(43, collect($areas)->sum(fn ($a) => count($a['tipos'])));

        $this->getJson('/api/roles')->assertForbidden(); // solo admin
    }
}
