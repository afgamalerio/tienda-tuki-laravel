<?php

use App\Exceptions\StockInsuficienteException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (StockInsuficienteException $exception) {
            return response()->json([
                'mensaje' => $exception->getMessage(),
                'errores' => [
                    'stock' => [
                        'producto' => $exception->producto,
                        'disponible' => $exception->disponible,
                        'solicitado' => $exception->solicitado,
                    ],
                ],
            ], 422);
        });

        $exceptions->render(function (UnauthorizedHttpException $exception) {
            return response()->json([
                'mensaje' => 'El token no existe, es inválido o expiró.',
            ], 401);
        });
    })->create();
