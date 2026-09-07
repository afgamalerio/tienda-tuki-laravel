<?php

namespace Tests\Unit;

use App\Data\CartSummaryData;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class CartSummaryDataTest extends TestCase
{
    public function test_calculates_subtotal_taxes_shipping_and_total(): void
    {
        $producto = new Producto([
            'nombre' => 'Soporte',
            'color' => 'Negro',
            'precio' => 100,
        ]);
        $item = new CarritoItem([
            'producto_id' => 1,
            'cantidad' => 2,
        ]);
        $item->setRelation('producto', $producto);
        $carrito = new Carrito;
        $carrito->setRelation('items', new Collection([$item]));

        $resumen = CartSummaryData::fromCart($carrito);

        self::assertSame(200.0, $resumen->subtotal);
        self::assertSame(42.0, $resumen->impuestos);
        self::assertSame(5000.0, $resumen->envio);
        self::assertSame(5242.0, $resumen->total);
    }

    public function test_applies_free_shipping_for_empty_or_high_value_carts(): void
    {
        $carritoVacio = new Carrito;
        $carritoVacio->setRelation('items', new Collection);

        self::assertSame(0.0, CartSummaryData::fromCart($carritoVacio)->envio);

        $producto = new Producto([
            'nombre' => 'Producto mayorista',
            'color' => 'Blanco',
            'precio' => 50000,
        ]);
        $item = new CarritoItem(['cantidad' => 1]);
        $item->setRelation('producto', $producto);
        $carrito = new Carrito;
        $carrito->setRelation('items', new Collection([$item]));

        self::assertSame(0.0, CartSummaryData::fromCart($carrito)->envio);
    }
}
