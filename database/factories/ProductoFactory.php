<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(3, true),
            'descripcion' => fake()->sentence(),
            'imagen' => null,
            'precio' => fake()->randomFloat(2, 100, 50000),
            'stock' => fake()->numberBetween(0, 100),
            'color' => fake()->safeColorName(),
            'categoria_id' => Categoria::factory(),
        ];
    }
}