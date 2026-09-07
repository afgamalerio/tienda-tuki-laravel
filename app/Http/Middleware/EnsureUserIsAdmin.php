<?php

namespace App\Http\Middleware;

use App\Enums\RolUsuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('api')?->rol !== RolUsuario::ADMIN) {
            return response()->json([
                'mensaje' => 'No tienes permisos para realizar esta operación.',
            ], 403);
        }

        return $next($request);
    }
}
