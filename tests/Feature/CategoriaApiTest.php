<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_category(): void
    {
        $response = $this->postJson('/api/v1/categorias', [
            'nombre' => 'Soportes',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('nombre', 'Soportes');

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Soportes',
        ]);
    }

    public function test_cannot_create_a_category_without_a_name(): void
    {
        $response = $this->postJson('/api/v1/categorias', []);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errores.nombre.0', 'El nombre de la categoría es obligatorio.');
    }

    public function test_returns_not_found_for_a_missing_category(): void
    {
        $this->getJson('/api/v1/categorias/999')
            ->assertNotFound()
            ->assertJsonPath('mensaje', 'Categoría no encontrada');
    }
}
