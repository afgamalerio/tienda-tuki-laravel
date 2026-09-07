<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_factories_create_related_catalog_data(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
        ]);

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
        ]);
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'categoria_id' => $categoria->id,
        ]);
        $this->assertTrue($producto->categoria->is($categoria));
    }
}
