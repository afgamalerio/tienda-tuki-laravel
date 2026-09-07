<?php

namespace Tests\Feature;

use App\Contracts\ProcesadorPago;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ProcesadorPagoTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_the_payment_processor_contract(): void
    {
        $producto = Producto::factory()->create([
            'precio' => 100,
            'stock' => 5,
        ]);
        $headers = $this->encabezadosAutenticados();
        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ], $headers)->assertCreated();

        $procesador = Mockery::mock(ProcesadorPago::class);
        $procesador->shouldReceive('cobrar')
            ->once()
            ->with(Mockery::type('App\\Models\\Pedido'));
        $this->app->instance(ProcesadorPago::class, $procesador);

        $this->postJson('/api/v1/checkout/confirmar', [
            'nombre_destinatario' => 'Ana Pérez',
            'direccion' => 'Calle 123',
            'ciudad' => 'Buenos Aires',
            'metodo_pago' => 'tarjeta',
        ], [...$headers, 'Idempotency-Key' => 'checkout-pago-mock'])->assertCreated();
    }

    public function test_payment_failure_rolls_back_order_stock_and_cart(): void
    {
        $producto = Producto::factory()->create([
            'precio' => 100,
            'stock' => 5,
        ]);
        $headers = $this->encabezadosAutenticados();
        $this->postJson('/api/v1/carrito/items', [
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ], $headers)->assertCreated();

        $procesador = Mockery::mock(ProcesadorPago::class);
        $procesador->shouldReceive('cobrar')
            ->once()
            ->andThrow(new RuntimeException('Pago rechazado'));
        $this->app->instance(ProcesadorPago::class, $procesador);
        $this->withoutExceptionHandling();

        try {
            $this->postJson('/api/v1/checkout/confirmar', [
                'nombre_destinatario' => 'Ana Pérez',
                'direccion' => 'Calle 123',
                'ciudad' => 'Buenos Aires',
                'metodo_pago' => 'tarjeta',
            ], [...$headers, 'Idempotency-Key' => 'checkout-pago-fallido']);
            self::fail('Se esperaba una excepción de pago.');
        } catch (RuntimeException $exception) {
            self::assertSame('Pago rechazado', $exception->getMessage());
        }

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_items', 0);
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'stock' => 5,
        ]);
    }
}
