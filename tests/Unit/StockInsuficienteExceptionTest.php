<?php

namespace Tests\Unit;

use App\Exceptions\StockInsuficienteException;
use PHPUnit\Framework\TestCase;

class StockInsuficienteExceptionTest extends TestCase
{
    public function test_exposes_stock_context_for_the_api_response(): void
    {
        $exception = new StockInsuficienteException('Soporte', 2, 5);

        self::assertSame('No hay stock suficiente para el producto.', $exception->getMessage());
        self::assertSame('Soporte', $exception->producto);
        self::assertSame(2, $exception->disponible);
        self::assertSame(5, $exception->solicitado);
    }
}