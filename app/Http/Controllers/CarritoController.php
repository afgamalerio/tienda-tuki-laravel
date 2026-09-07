<?php

namespace App\Http\Controllers;

use App\Data\CartSummaryData;
use App\Data\CheckoutData;
use App\Exceptions\StockInsuficienteException;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\User;
use App\Services\ConfirmarCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    public function __construct(private readonly ConfirmarCompra $confirmarCompra) {}

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
        $producto = Producto::find($productoId);

        if (! $producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado',
            ], 404);
        }

        $item = $carrito->items->firstWhere('producto_id', $productoId);

        if (! $item) {
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

        if (! $item) {
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
            'resumen' => CartSummaryData::fromCart(
                $this->obtenerCarrito($request)->load('items.producto')
            )->toArray(),
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
        /** @var User $usuario */
        $usuario = $request->user('api');
        $datos = CheckoutData::fromArray($request->validated());
        $pedido = $this->confirmarCompra->ejecutar(
            $usuario,
            $datos,
            $request->validated()['idempotency_key'],
        );

        if ($pedido === null) {
            return response()->json([
                'mensaje' => 'No se puede confirmar un carrito vacío',
            ], 422);
        }

        return response()->json([
            'mensaje' => 'Compra confirmada correctamente',
            'pedido' => $pedido,
        ], $pedido->wasRecentlyCreated ? 201 : 200);
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
