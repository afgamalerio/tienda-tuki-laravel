<?php

namespace Tests\Feature;

use App\Contracts\ProcesadorPago;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
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
        ], $headers)->assertCreated();
    }
}
