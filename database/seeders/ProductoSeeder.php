<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            'Soportes' => ['Soporte Tuki', 'Negro', 8500],
            'Cerámica' => ['Taza Tuki', 'Blanco', 12000],
            'Llaveros' => ['Llavero Tuki', 'Rojo', 2500],
            'Decoración' => ['Adorno Tuki', 'Azul', 15000],
        ];

        foreach ($productos as $categoriaNombre => [$nombre, $color, $precio]) {
            $categoria = Categoria::where('nombre', $categoriaNombre)->firstOrFail();

            Producto::updateOrCreate(
                ['nombre' => $nombre, 'color' => $color],
                [
                    'descripcion' => 'Producto inicial de Tienda Tuki',
                    'precio' => $precio,
                    'stock' => 10,
                    'categoria_id' => $categoria->id,
                ]
            );
        }
    }
}