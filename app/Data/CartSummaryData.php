<?php

namespace App\Data;

use App\Models\Carrito;

class CartSummaryData
{
    public function __construct(
        public readonly array $items,
        public readonly float $subtotal,
        public readonly float $impuestos,
        public readonly float $envio,
        public readonly float $total,
    ) {
    }

    public static function fromCart(Carrito $carrito): self
    {
        $items = $carrito->items->map(function ($item): array {
            $subtotal = round((float) $item->precio_unitario * $item->cantidad, 2);

            return [
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto->nombre,
                'color' => $item->producto->color,
                'cantidad' => $item->cantidad,
                'precio_unitario' => (float) $item->precio_unitario,
                'subtotal' => $subtotal,
            ];
        })->values()->all();

        $subtotal = round(array_sum(array_column($items, 'subtotal')), 2);
        $impuestos = round($subtotal * 0.21, 2);
        $envio = $subtotal >= 50000 || $subtotal === 0 ? 0.0 : 5000.0;

        return new self(
            $items,
            $subtotal,
            $impuestos,
            $envio,
            round($subtotal + $impuestos + $envio, 2),
        );
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'impuestos' => $this->impuestos,
            'envio' => $this->envio,
            'total' => $this->total,
        ];
    }
}
