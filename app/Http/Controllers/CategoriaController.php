<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

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

        return response()->json($categoria);
    }

    public function store(Request $request)
    {
        $categoria = new Categoria();

        $categoria->nombre = $request->nombre;

        $categoria->save();

        return response()->json($categoria, 201);
    }

    public function update(Request $request, int $id)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return response()->json(['mensaje' => 'Categoría no encontrada'], 404);
        }

        $categoria->nombre = $request->nombre;
        $categoria->save();

        return response()->json($categoria);
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
