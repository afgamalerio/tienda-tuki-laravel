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
            ->assertJsonPath('mensaje', 'Categoría creada correctamente')
            ->assertJsonPath('categoria.nombre', 'Soportes');

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

    public function test_cannot_create_a_category_with_a_duplicate_name(): void
    {
        $this->postJson('/api/v1/categorias', ['nombre' => 'Soportes']);

        $this->postJson('/api/v1/categorias', ['nombre' => 'Soportes'])
            ->assertStatus(422)
            ->assertJsonPath('errores.nombre.0', 'Ya existe una categoría con ese nombre.');
    }

    public function test_returns_not_found_for_a_missing_category(): void
    {
        $this->getJson('/api/v1/categorias/999')
            ->assertNotFound()
            ->assertJsonPath('mensaje', 'Categoría no encontrada');
    }

    public function test_can_update_a_category(): void
    {
        $categoria = $this->postJson('/api/v1/categorias', [
            'nombre' => 'Soportes',
        ])->json('categoria');

        $this->putJson('/api/v1/categorias/'.$categoria['id'], [
            'nombre' => 'Accesorios',
        ])
            ->assertOk()
            ->assertJsonPath('mensaje', 'Categoría actualizada correctamente')
            ->assertJsonPath('categoria.nombre', 'Accesorios');

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria['id'],
            'nombre' => 'Accesorios',
        ]);
    }

    public function test_cannot_update_a_category_with_a_duplicate_name(): void
    {
        $this->postJson('/api/v1/categorias', ['nombre' => 'Soportes']);
        $categoria = $this->postJson('/api/v1/categorias', [
            'nombre' => 'Accesorios',
        ])->json('categoria');

        $this->putJson('/api/v1/categorias/'.$categoria['id'], [
            'nombre' => 'Soportes',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errores.nombre.0', 'Ya existe una categoría con ese nombre.');
    }

    public function test_can_delete_a_category(): void
    {
        $categoria = $this->postJson('/api/v1/categorias', [
            'nombre' => 'Soportes',
        ])->json('categoria');

        $this->deleteJson('/api/v1/categorias/'.$categoria['id'])
            ->assertOk()
            ->assertJsonPath('mensaje', 'Categoría eliminada correctamente');

        $this->assertDatabaseMissing('categorias', [
            'id' => $categoria['id'],
        ]);
    }

    public function test_returns_not_found_when_updating_a_missing_category(): void
    {
        $this->putJson('/api/v1/categorias/999', [
            'nombre' => 'Accesorios',
        ])
            ->assertNotFound()
            ->assertJsonPath('mensaje', 'Categoría no encontrada');
    }

    public function test_returns_not_found_when_deleting_a_missing_category(): void
    {
        $this->deleteJson('/api/v1/categorias/999')
            ->assertNotFound()
            ->assertJsonPath('mensaje', 'Categoría no encontrada');
    }
}
