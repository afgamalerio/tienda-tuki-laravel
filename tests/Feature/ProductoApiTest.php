<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_product(): void
    {
        $categoria = Categoria::create(['nombre' => 'Soportes']);

        $response = $this->postJson('/api/v1/productos', [
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('mensaje', 'Producto creado correctamente')
            ->assertJsonPath('producto.nombre', 'Soporte para celular');

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Soporte para celular',
            'color' => 'Negro',
            'categoria_id' => $categoria->id,
        ]);
    }

    public function test_can_list_products(): void
    {
        $categoria = Categoria::create(['nombre' => 'Soportes']);
        Producto::create([
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ]);

        $this->getJson('/api/v1/productos')
            ->assertOk()
            ->assertJsonPath('mensaje', 'Listado de productos')
            ->assertJsonCount(1, 'productos');
    }

    public function test_can_update_a_product(): void
    {
        $categoria = Categoria::create(['nombre' => 'Soportes']);
        $producto = Producto::create([
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ]);

        $this->putJson('/api/v1/productos/'.$producto->id, [
            ...$this->productData(),
            'nombre' => 'Soporte actualizado',
            'categoria_id' => $categoria->id,
        ])
            ->assertOk()
            ->assertJsonPath('mensaje', 'Producto actualizado correctamente')
            ->assertJsonPath('producto.nombre', 'Soporte actualizado');

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'Soporte actualizado',
        ]);
    }

    public function test_can_delete_a_product(): void
    {
        $categoria = Categoria::create(['nombre' => 'Soportes']);
        $producto = Producto::create([
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ]);

        $this->deleteJson('/api/v1/productos/'.$producto->id)
            ->assertOk()
            ->assertJsonPath('mensaje', 'Producto eliminado correctamente');

        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);
    }

    public function test_cannot_create_a_product_with_a_duplicate_name_and_color(): void
    {
        $categoria = Categoria::create(['nombre' => 'Soportes']);
        Producto::create([
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ]);

        $response = $this->postJson('/api/v1/productos', [
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errores.color.0', 'Ya existe otro producto con ese nombre y color.');
    }

    private function productData(): array
    {
        return [
            'nombre' => 'Soporte para celular',
            'descripcion' => 'Soporte práctico para celular',
            'precio' => 8500,
            'stock' => 10,
            'color' => 'Negro',
        ];
    }
}
