<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Soportes', 'Cerámica', 'Llaveros', 'Decoración'] as $nombre) {
            Categoria::firstOrCreate(['nombre' => $nombre]);
        }
    }
}