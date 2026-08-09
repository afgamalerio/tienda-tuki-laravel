<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UsuarioController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/productos', [ProductoController::class, 'index'])
        ->name('api.productos');
    Route::get('/categorias', [CategoriaController::class, 'index'])
        ->name('api.categorias');
    Route::get('/usuarios', [UsuarioController::class, 'index'])
        ->name('api.usuarios');
});
