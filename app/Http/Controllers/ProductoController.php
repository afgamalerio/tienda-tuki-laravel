<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductoResource;
use App\Models\CarritoItem;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $porPagina = min(max($request->integer('per_page', 15), 1), 100);
        $productos = Producto::paginate($porPagina);

        return response()->json([
            'mensaje' => 'Listado de productos',
            'productos' => [
                'data' => ProductoResource::collection($productos->getCollection())->resolve(),
                'links' => [
                    'first' => $productos->url(1),
                    'last' => $productos->url($productos->lastPage()),
                    'prev' => $productos->previousPageUrl(),
                    'next' => $productos->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $productos->currentPage(),
                    'from' => $productos->firstItem(),
                    'last_page' => $productos->lastPage(),
                    'per_page' => $productos->perPage(),
                    'to' => $productos->lastItem(),
                    'total' => $productos->total(),
                ],
            ],
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $producto = Producto::create($request->validated());

        return response()->json([
            'mensaje' => 'Producto creado correctamente',
            'producto' => new ProductoResource($producto),
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
            'producto' => new ProductoResource($producto),
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
            'producto' => new ProductoResource($producto),
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
