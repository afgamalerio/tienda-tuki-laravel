<?php

namespace Tests\Unit;

use App\Data\CheckoutData;
use PHPUnit\Framework\TestCase;

class CheckoutDataTest extends TestCase
{
    public function test_creates_data_from_validated_array(): void
    {
        $datos = CheckoutData::fromArray([
            'nombre_destinatario' => 'Ana Pérez',
            'direccion' => 'Calle 123',
            'ciudad' => 'Buenos Aires',
            'metodo_pago' => 'tarjeta',
        ]);

        self::assertSame('Ana Pérez', $datos->nombreDestinatario);
        self::assertSame('Calle 123', $datos->direccion);
        self::assertSame('Buenos Aires', $datos->ciudad);
        self::assertSame('tarjeta', $datos->metodoPago);
    }

    public function test_converts_data_back_to_persistence_array(): void
    {
        $datos = new CheckoutData(
            'Ana Pérez',
            'Calle 123',
            'Buenos Aires',
            'transferencia',
        );

        self::assertSame([
            'nombre_destinatario' => 'Ana Pérez',
            'direccion' => 'Calle 123',
            'ciudad' => 'Buenos Aires',
            'metodo_pago' => 'transferencia',
        ], $datos->toArray());
    }
}
