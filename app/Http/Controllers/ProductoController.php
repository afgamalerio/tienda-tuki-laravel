<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::all();

        return response()->json([
            'mensaje' => 'Listado de productos',
            'productos' => $productos
        ]);
    }

    public function show(int $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'mensaje' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json($producto);
    }

    public function store(StoreProductRequest $request)
    {
        $producto = new Producto();

        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->imagen = $request->imagen;
        $producto->precio = $request->precio;
        $producto->stock = $request->stock;
        $producto->color = $request->color;
        $producto->categoria_id = $request->categoria_id;

        $producto->save();

        return response()->json($producto, 201);
    }

    public function update(UpdateProductRequest $request, int $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }

        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->imagen = $request->imagen;
        $producto->precio = $request->precio;
        $producto->stock = $request->stock;
        $producto->color = $request->color;
        $producto->categoria_id = $request->categoria_id;

        $producto->save();

        return response()->json($producto);
    }

    public function destroy(int $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }

        $producto->delete();

        return response()->json(['mensaje' => 'Producto eliminado correctamente']);
    }
}
