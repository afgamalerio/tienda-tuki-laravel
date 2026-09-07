<?php

namespace Tests\Unit;

use App\Models\Producto;
use App\Rules\UniqueProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_an_existing_name_and_color_combination(): void
    {
        Producto::factory()->create([
            'nombre' => 'Soporte',
            'color' => 'Negro',
        ]);
        $mensajes = [];
        $regla = new UniqueProductVariant('Soporte', 'Negro');

        $regla->validate('color', 'Negro', function (string $mensaje) use (&$mensajes): void {
            $mensajes[] = $mensaje;
        });

        self::assertSame(['Ya existe otro producto con ese nombre y color.'], $mensajes);
    }

    public function test_allows_a_different_color_for_the_same_product_name(): void
    {
        Producto::factory()->create([
            'nombre' => 'Soporte',
            'color' => 'Negro',
        ]);
        $mensajes = [];
        $regla = new UniqueProductVariant('Soporte', 'Rojo');

        $regla->validate('color', 'Rojo', function (string $mensaje) use (&$mensajes): void {
            $mensajes[] = $mensaje;
        });

        self::assertSame([], $mensajes);
    }

    public function test_ignores_the_product_being_updated(): void
    {
        $producto = Producto::factory()->create([
            'nombre' => 'Soporte',
            'color' => 'Negro',
        ]);
        $mensajes = [];
        $regla = new UniqueProductVariant('Soporte', 'Negro', $producto->id);

        $regla->validate('color', 'Negro', function (string $mensaje) use (&$mensajes): void {
            $mensajes[] = $mensaje;
        });

        self::assertSame([], $mensajes);
    }
}
