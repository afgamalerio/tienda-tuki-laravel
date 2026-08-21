<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarritoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_a_product_and_get_its_summary(): void
    {
        $producto = $this->createProduct(price: 100, stock: 10);
        $headers = ['X-Session-Id' => 'cliente-1'];

        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ], $headers)->assertCreated();

        $this->getJson('/api/v1/carrito/resumen', $headers)
            ->assertOk()
            ->assertJsonPath('resumen.items.0.cantidad', 2)
            ->assertJsonPath('resumen.subtotal', 200)
            ->assertJsonPath('resumen.impuestos', 42)
            ->assertJsonPath('resumen.envio', 5000)
            ->assertJsonPath('resumen.total', 5242);
    }

    public function test_can_update_remove_and_clear_cart_items(): void
    {
        $producto = $this->createProduct();
        $headers = ['X-Session-Id' => 'cliente-2'];

        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ], $headers);

        $this->putJson('/api/v1/carrito/items/'.$producto->id, [
            'cantidad' => 3,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('carrito.items.0.cantidad', 3);

        $this->deleteJson('/api/v1/carrito/items/'.$producto->id, [], $headers)
            ->assertOk();

        $this->deleteJson('/api/v1/carrito', [], $headers)
            ->assertOk()
            ->assertJsonCount(0, 'carrito.items');
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $producto = $this->createProduct(stock: 2);

        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 3,
        ], ['X-Session-Id' => 'cliente-3'])
            ->assertStatus(422)
            ->assertJsonPath('mensaje', 'No hay stock suficiente para el producto.')
            ->assertJsonPath('errores.stock.disponible', 2);
    }

    public function test_checkout_decreases_stock_and_clears_the_cart(): void
    {
        $producto = $this->createProduct(price: 100, stock: 5);
        $headers = ['X-Session-Id' => 'cliente-4'];

        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ], $headers);

        $response = $this->postJson('/api/v1/checkout/confirmar', [
            'nombre_destinatario' => 'Ana Pérez',
            'direccion' => 'Calle 123',
            'ciudad' => 'Buenos Aires',
            'metodo_pago' => 'tarjeta',
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('mensaje', 'Compra confirmada correctamente')
            ->assertJsonPath('pedido.items.0.cantidad', 2);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'stock' => 3,
        ]);
        $this->assertDatabaseCount('pedido_items', 1);
        $this->assertDatabaseCount('carrito_items', 0);
    }

    private function createProduct(float $price = 8500, int $stock = 10): Producto
    {
        $categoria = Categoria::create(['nombre' => uniqid('categoria_')]);

        return Producto::create([
            'nombre' => uniqid('producto_'),
            'descripcion' => 'Producto de prueba',
            'precio' => $price,
            'stock' => $stock,
            'color' => 'Negro',
            'categoria_id' => $categoria->id,
        ]);
    }
}
