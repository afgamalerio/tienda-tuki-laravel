<?php

namespace App\Rules;

use Closure;
use App\Models\Producto;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueProductVariant implements ValidationRule
{
    private string $nombre;
    private string $color;
    private ?int $productoId;

    public function __construct(string $nombre, string $color, ?int $productoId = null)
    {
        $this->nombre = $nombre;
        $this->color = $color;
        $this->productoId = $productoId;
    }
    
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Producto::where('nombre', $this->nombre)
            ->where('color', $this->color);

        if ($this->productoId !== null) {
            $query->where('id', '!=', $this->productoId);
        }

        if ($query->exists()) {
            $fail('Ya existe otro producto con ese nombre y color.');
        }
    }
}
