<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\CarritoItem;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function index()
    {
        return response()->json([
            'mensaje' => 'Listado de productos',
            'productos' => Producto::all(),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $producto = Producto::create($request->validated());

        return response()->json([
            'mensaje' => 'Producto creado correctamente',
            'producto' => $producto,
        ], 201);
    }

    public function show(int $id)
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado',
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Producto encontrado',
            'producto' => $producto,
        ]);
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado',
            ], 404);
        }

        $producto->update($request->validated());

        return response()->json([
            'mensaje' => 'Producto actualizado correctamente',
            'producto' => $producto,
        ]);
    }

    public function destroy(int $id)
    {
        $producto = Producto::find($id);

        if (! $producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado',
            ], 404);
        }

        if (CarritoItem::where('producto_id', $producto->id)->exists()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar un producto presente en un carrito.',
            ], 409);
        }

        $producto->delete();

        return response()->json([
            'mensaje' => 'Producto eliminado correctamente',
        ]);
    }
}
