<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class CarritoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_requires_authentication(): void
    {
        $this->getJson('/api/v1/carrito')
            ->assertUnauthorized()
            ->assertJsonPath('mensaje', 'El token no existe, es inválido o expiró.');
    }

    public function test_cart_rejects_an_invalid_token(): void
    {
        $this->getJson('/api/v1/carrito', [
            'Authorization' => 'Bearer token-invalido',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('mensaje', 'El token no existe, es inválido o expiró.');
    }

    public function test_cart_rejects_an_expired_token(): void
    {
        $token = JWTAuth::fromUser(User::factory()->create());
        Carbon::setTestNow(now()->addMinutes(config('jwt.ttl') + 1));

        $this->getJson('/api/v1/carrito', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertUnauthorized()
            ->assertJsonPath('mensaje', 'El token no existe, es inválido o expiró.');

        Carbon::setTestNow();
    }

    public function test_can_add_a_product_and_get_its_summary(): void
    {
        $producto = $this->createProduct(price: 100, stock: 10);
        $headers = $this->encabezadosAutenticados();

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
        $headers = $this->encabezadosAutenticados();

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

    public function test_returns_not_found_when_removing_a_product_not_in_the_cart(): void
    {
        $producto = $this->createProduct();

        $this->deleteJson('/api/v1/carrito/items/'.$producto->id, [], $this->encabezadosAutenticados())
            ->assertNotFound()
            ->assertJsonPath('mensaje', 'El producto no está en el carrito');
    }

    public function test_returns_not_found_when_updating_a_missing_product(): void
    {
        $this->putJson('/api/v1/carrito/items/999999', [
            'cantidad' => 2,
        ], $this->encabezadosAutenticados())
            ->assertNotFound()
            ->assertJsonPath('mensaje', 'Producto no encontrado');
    }

    public function test_each_user_can_only_access_their_own_cart(): void
    {
        $producto = $this->createProduct();
        $usuarioUno = \App\Models\User::factory()->create();
        $usuarioDos = \App\Models\User::factory()->create();

        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ], [
            ...$this->encabezadosAutenticados($usuarioUno),
            'X-Session-Id' => 'carrito-del-usuario-uno',
        ])->assertCreated();

        $this->getJson('/api/v1/carrito', [
            ...$this->encabezadosAutenticados($usuarioDos),
            'X-Session-Id' => 'carrito-del-usuario-uno',
        ])
            ->assertOk()
            ->assertJsonCount(0, 'carrito.items');
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $producto = $this->createProduct(stock: 2);

        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 3,
        ], $this->encabezadosAutenticados())
            ->assertStatus(422)
            ->assertJsonPath('mensaje', 'No hay stock suficiente para el producto.')
            ->assertJsonPath('errores.stock.disponible', 2);
    }

    public function test_checkout_decreases_stock_and_clears_the_cart(): void
    {
        $producto = $this->createProduct(price: 100, stock: 5);
        $headers = $this->encabezadosAutenticados();

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
