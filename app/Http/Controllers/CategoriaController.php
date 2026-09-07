<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $porPagina = min(max($request->integer('per_page', 15), 1), 100);
        $categorias = Categoria::paginate($porPagina);

        return response()->json([
            'mensaje' => 'Listado de categorías',
            'categorias' => [
                'data' => CategoriaResource::collection($categorias->getCollection())->resolve(),
                'links' => [
                    'first' => $categorias->url(1),
                    'last' => $categorias->url($categorias->lastPage()),
                    'prev' => $categorias->previousPageUrl(),
                    'next' => $categorias->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $categorias->currentPage(),
                    'from' => $categorias->firstItem(),
                    'last_page' => $categorias->lastPage(),
                    'per_page' => $categorias->perPage(),
                    'to' => $categorias->lastItem(),
                    'total' => $categorias->total(),
                ],
            ],
        ]);
    }

    public function show(int $id)
    {
        $categoria = Categoria::find($id);

        if (! $categoria) {
            return response()->json([
                'mensaje' => 'Categoría no encontrada',
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Categoría encontrada',
            'categoria' => new CategoriaResource($categoria),
        ]);
    }

    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());

        return response()->json([
            'mensaje' => 'Categoría creada correctamente',
            'categoria' => new CategoriaResource($categoria),
        ], 201);
    }

    public function update(UpdateCategoriaRequest $request, int $id)
    {
        $categoria = Categoria::find($id);

        if (! $categoria) {
            return response()->json(['mensaje' => 'Categoría no encontrada'], 404);
        }

        $categoria->update($request->validated());

        return response()->json([
            'mensaje' => 'Categoría actualizada correctamente',
            'categoria' => new CategoriaResource($categoria),
        ]);
    }

    public function destroy(int $id)
    {
        $categoria = Categoria::find($id);

        if (! $categoria) {
            return response()->json(['mensaje' => 'Categoría no encontrada'], 404);
        }

        if ($categoria->productos()->exists()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar una categoría con productos asociados.',
            ], 409);
        }

        $categoria->delete();

        return response()->json(['mensaje' => 'Categoría eliminada correctamente']);
    }
}
