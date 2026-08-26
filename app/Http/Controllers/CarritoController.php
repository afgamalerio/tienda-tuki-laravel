<?php

namespace App\Http\Controllers;

use App\Data\CartSummaryData;
use App\Data\CheckoutData;
use App\Exceptions\StockInsuficienteException;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'mensaje' => 'Carrito obtenido correctamente',
            'carrito' => $this->obtenerCarrito($request)->load('items.producto'),
        ]);
    }

    public function store(AddCartItemRequest $request)
    {
        $carrito = DB::transaction(function () use ($request) {
            $producto = Producto::lockForUpdate()->findOrFail($request->producto_id);
            $carrito = $this->obtenerCarrito($request);
            $item = $carrito->items->firstWhere('producto_id', $producto->id);
            $cantidad = $request->cantidad + ($item?->cantidad ?? 0);

            $this->validarStock($producto, $cantidad);

            if ($item) {
                $item->update([
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                ]);
            } else {
                $carrito->items()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $request->cantidad,
                    'precio_unitario' => $producto->precio,
                ]);
            }

            return $carrito->fresh('items.producto');
        });

        return response()->json([
            'mensaje' => 'Producto agregado al carrito',
            'carrito' => $carrito,
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, int $productoId)
    {
        $carrito = $this->obtenerCarrito($request);
        $producto = Producto::findOrFail($productoId);
        $item = $carrito->items->firstWhere('producto_id', $productoId);

        if (!$item) {
            return response()->json([
                'mensaje' => 'El producto no está en el carrito',
            ], 404);
        }

        $this->validarStock($producto, $request->cantidad);
        $item->update(['cantidad' => $request->cantidad]);

        return response()->json([
            'mensaje' => 'Cantidad actualizada correctamente',
            'carrito' => $carrito->fresh('items.producto'),
        ]);
    }

    public function destroy(Request $request, int $productoId)
    {
        $carrito = $this->obtenerCarrito($request);
        $item = $carrito->items()->where('producto_id', $productoId)->first();

        if (!$item) {
            return response()->json([
                'mensaje' => 'El producto no está en el carrito',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'mensaje' => 'Producto eliminado del carrito',
            'carrito' => $carrito->fresh('items.producto'),
        ]);
    }

    public function clear(Request $request)
    {
        $carrito = $this->obtenerCarrito($request);
        $carrito->items()->delete();

        return response()->json([
            'mensaje' => 'Carrito vaciado correctamente',
            'carrito' => $carrito->fresh('items.producto'),
        ]);
    }

    public function summary(Request $request)
    {
        return response()->json([
            'mensaje' => 'Resumen del carrito obtenido correctamente',
            'resumen' => CartSummaryData::fromCart($this->obtenerCarrito($request))->toArray(),
        ]);
    }

    public function review(Request $request)
    {
        return response()->json([
            'mensaje' => 'Carrito listo para confirmar',
            'resumen' => CartSummaryData::fromCart(
                $this->obtenerCarrito($request)->load('items.producto')
            )->toArray(),
        ]);
    }

    public function confirm(CheckoutRequest $request)
    {
        $carrito = $this->obtenerCarrito($request)->load('items.producto');

        if ($carrito->items->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se puede confirmar un carrito vacío',
            ], 422);
        }

        $pedido = DB::transaction(function () use ($request): Pedido {
            $carrito = $this->obtenerCarrito($request)->load('items.producto');

            foreach ($carrito->items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item->producto_id);
                $this->validarStock($producto, $item->cantidad);
            }

            $resumen = CartSummaryData::fromCart($carrito);
            $datos = CheckoutData::fromArray($request->validated());
            $pedido = Pedido::create([
                'user_id' => $carrito->user_id,
                'session_id' => $carrito->session_id,
                'subtotal' => $resumen->subtotal,
                'impuestos' => $resumen->impuestos,
                'envio' => $resumen->envio,
                'total' => $resumen->total,
                ...$datos->toArray(),
            ]);

            foreach ($carrito->items as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item->producto_id);
                $producto->decrement('stock', $item->cantidad);
                $pedido->items()->create([
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'color' => $producto->color,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => round((float) $item->precio_unitario * $item->cantidad, 2),
                ]);
            }

            $carrito->items()->delete();

            return $pedido->load('items');
        });

        return response()->json([
            'mensaje' => 'Compra confirmada correctamente',
            'pedido' => $pedido,
        ], 201);
    }

    private function obtenerCarrito(Request $request): Carrito
    {
        $usuario = $request->user('api');

        return Carrito::firstOrCreate(
            ['user_id' => $usuario->id],
            ['session_id' => 'usuario-'.$usuario->id]
        );
    }

    private function validarStock(Producto $producto, int $cantidad): void
    {
        if ($producto->stock < $cantidad) {
            throw new StockInsuficienteException(
                $producto->nombre,
                $producto->stock,
                $cantidad,
            );
        }
    }
}
