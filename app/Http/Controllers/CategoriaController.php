<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::all();

        return response()->json([
            'mensaje' => 'Listado de categorías',
            'categorias' => $categorias
        ]);
    }

    public function show(int $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json([
                'mensaje' => 'Categoría no encontrada'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Categoría encontrada',
            'categoria' => $categoria,
        ]);
    }

    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());

        return response()->json([
            'mensaje' => 'Categoría creada correctamente',
            'categoria' => $categoria,
        ], 201);
    }

    public function update(UpdateCategoriaRequest $request, int $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['mensaje' => 'Categoría no encontrada'], 404);
        }

        $categoria->update($request->validated());

        return response()->json([
            'mensaje' => 'Categoría actualizada correctamente',
            'categoria' => $categoria,
        ]);
    }

    public function destroy(int $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['mensaje' => 'Categoría no encontrada'], 404);
        }

        $categoria->delete();

        return response()->json(['mensaje' => 'Categoría eliminada correctamente']);
    }
}
