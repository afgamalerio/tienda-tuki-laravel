<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    //Rutas de la API para productos
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);


    //Rutas de la API para categorías
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::post('/categorias', [CategoriaController::class, 'store']);
    Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
    Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

    //Rutas de la API para usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])
        ->name('api.usuarios');

    Route::middleware('jwt.auth')->group(function () {
        // Rutas del carrito y checkout
        Route::get('/carrito', [CarritoController::class, 'index']);
        Route::post('/carrito/items', [CarritoController::class, 'store']);
        Route::put('/carrito/items/{productoId}', [CarritoController::class, 'update']);
        Route::delete('/carrito/items/{productoId}', [CarritoController::class, 'destroy']);
        Route::delete('/carrito', [CarritoController::class, 'clear']);
        Route::get('/carrito/resumen', [CarritoController::class, 'summary']);
        Route::get('/checkout/revisar', [CarritoController::class, 'review']);
        Route::post('/checkout/confirmar', [CarritoController::class, 'confirm']);
    });
});
