<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        Categoria::query()->each(function (Categoria $categoria): void {
            Producto::factory()->create([
                'categoria_id' => $categoria->id,
            ]);
        });
    }
}