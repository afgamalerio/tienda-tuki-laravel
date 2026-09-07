<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'imagen' => $this->imagen,
            'precio' => (float) $this->precio,
            'stock' => $this->stock,
            'color' => $this->color,
            'categoria_id' => $this->categoria_id,
        ];
    }
}
