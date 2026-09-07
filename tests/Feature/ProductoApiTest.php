<?php

namespace Tests\Feature;

use App\Models\Carrito;
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
        ], $this->encabezadosAdmin());

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
            ->assertJsonCount(1, 'productos.data')
            ->assertJsonStructure(['productos' => ['data', 'links', 'meta']]);
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
        ], $this->encabezadosAdmin())
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

        $this->deleteJson('/api/v1/productos/'.$producto->id, [], $this->encabezadosAdmin())
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
        ], $this->encabezadosAdmin());

        $response
            ->assertStatus(422)
            ->assertJsonPath('errores.color.0', 'Ya existe otro producto con ese nombre y color.');
    }

    public function test_cannot_create_a_product_with_a_missing_category(): void
    {
        $this->postJson('/api/v1/productos', [
            ...$this->productData(),
            'categoria_id' => 999,
        ], $this->encabezadosAdmin())
            ->assertStatus(422)
            ->assertJsonPath('errores.categoria_id.0', 'La categoría seleccionada no existe.');
    }

    public function test_cannot_create_a_product_without_required_data(): void
    {
        $this->postJson('/api/v1/productos', [], $this->encabezadosAdmin())
            ->assertStatus(422)
            ->assertJsonStructure([
                'mensaje',
                'errores' => [
                    'nombre',
                    'descripcion',
                    'precio',
                    'stock',
                    'color',
                    'categoria_id',
                ],
            ]);
    }

    public function test_cannot_update_a_product_to_a_duplicate_name_and_color(): void
    {
        $categoria = Categoria::create(['nombre' => 'Soportes']);
        $productoOriginal = Producto::create([
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ]);
        $productoAActualizar = Producto::create([
            ...$this->productData(),
            'nombre' => 'Otro soporte',
            'color' => 'Azul',
            'categoria_id' => $categoria->id,
        ]);

        $this->putJson('/api/v1/productos/'.$productoAActualizar->id, [
            ...$this->productData(),
            'categoria_id' => $categoria->id,
        ], $this->encabezadosAdmin())
            ->assertStatus(422)
            ->assertJsonPath('errores.color.0', 'Ya existe otro producto con ese nombre y color.');

        $this->assertDatabaseHas('productos', [
            'id' => $productoOriginal->id,
            'nombre' => 'Soporte para celular',
            'color' => 'Negro',
        ]);
    }

    public function test_product_writes_require_an_admin(): void
    {
        $datos = [
            ...$this->productData(),
            'categoria_id' => Categoria::create(['nombre' => 'Soportes'])->id,
        ];

        $this->postJson('/api/v1/productos', $datos)
            ->assertUnauthorized();

        $this->postJson('/api/v1/productos', $datos, $this->encabezadosAutenticados())
            ->assertForbidden()
            ->assertJsonPath('mensaje', 'No tienes permisos para realizar esta operación.');

        $this->postJson('/api/v1/productos', $datos, $this->encabezadosAdmin())
            ->assertCreated();
    }

    public function test_product_validation_rejects_oversized_fields(): void
    {
        $datos = [
            ...$this->productData(),
            'nombre' => str_repeat('a', 256),
            'categoria_id' => Categoria::factory()->create()->id,
        ];

        $this->postJson('/api/v1/productos', $datos, $this->encabezadosAdmin())
            ->assertUnprocessable()
            ->assertJsonStructure(['errores' => ['nombre']]);
    }

    public function test_cannot_delete_a_product_present_in_a_cart(): void
    {
        $producto = Producto::factory()->create();
        $carrito = Carrito::create([
            'session_id' => 'carrito-prueba',
        ]);
        $carrito->items()->create([
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => $producto->precio,
        ]);

        $this->deleteJson('/api/v1/productos/'.$producto->id, [], $this->encabezadosAdmin())
            ->assertStatus(409)
            ->assertJsonPath('mensaje', 'No se puede eliminar un producto presente en un carrito.');

        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
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
