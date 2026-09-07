<?php

namespace App\Services;

use App\Contracts\ProcesadorPago;
use App\Data\CartSummaryData;
use App\Data\CheckoutData;
use App\Exceptions\StockInsuficienteException;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConfirmarCompra
{
    public function __construct(private readonly ProcesadorPago $procesadorPago) {}

    public function ejecutar(User $usuario, CheckoutData $datos, string $idempotencyKey): ?Pedido
    {
        return DB::transaction(function () use ($usuario, $datos, $idempotencyKey): ?Pedido {
            $pedidoExistente = Pedido::where('user_id', $usuario->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($pedidoExistente) {
                return $pedidoExistente->load('items');
            }

            $carrito = Carrito::firstOrCreate(
                ['user_id' => $usuario->id],
                ['session_id' => 'usuario-'.$usuario->id]
            );
            $carrito = Carrito::whereKey($carrito->id)
                ->lockForUpdate()
                ->with('items.producto')
                ->firstOrFail();

            if ($carrito->items->isEmpty()) {
                return null;
            }

            foreach ($carrito->items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item->producto_id);

                if ($producto->stock < $item->cantidad) {
                    throw new StockInsuficienteException(
                        $producto->nombre,
                        $producto->stock,
                        $item->cantidad,
                    );
                }
            }

            $resumen = CartSummaryData::fromCart($carrito);
            $pedido = Pedido::create([
                'user_id' => $carrito->user_id,
                'session_id' => $carrito->session_id,
                'idempotency_key' => $idempotencyKey,
                'subtotal' => $resumen->subtotal,
                'impuestos' => $resumen->impuestos,
                'envio' => $resumen->envio,
                'total' => $resumen->total,
                ...$datos->toArray(),
            ]);

            $this->procesadorPago->cobrar($pedido);

            foreach ($carrito->items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item->producto_id);
                $precio = (float) $producto->precio;
                $producto->decrement('stock', $item->cantidad);
                $pedido->items()->create([
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'color' => $producto->color,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => round($precio * $item->cantidad, 2),
                ]);
            }

            $carrito->items()->delete();

            return $pedido->load('items');
        });
    }
}
