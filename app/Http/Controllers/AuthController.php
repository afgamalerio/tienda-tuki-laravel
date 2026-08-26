<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'usuario' => $request->user('api')->only(['id', 'name', 'email']),
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($usuario);

        return response()->json([
            'mensaje' => 'Usuario registrado correctamente',
            'usuario' => $usuario->only(['id', 'name', 'email']),
            'token' => $token,
            'tipo_token' => 'Bearer',
            'expira_en' => config('jwt.ttl') * 60,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credenciales = $request->validated();
        $token = JWTAuth::attempt($credenciales);

        if (!$token) {
            return response()->json([
                'mensaje' => 'Las credenciales son incorrectas',
            ], 401);
        }

        return response()->json([
            'mensaje' => 'Login realizado correctamente',
            'usuario' => User::where('email', $credenciales['email'])
                ->firstOrFail()
                ->only(['id', 'name', 'email']),
            'token' => $token,
            'tipo_token' => 'Bearer',
            'expira_en' => config('jwt.ttl') * 60,
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'mensaje' => 'Token renovado correctamente',
            'token' => JWTAuth::refresh(),
            'tipo_token' => 'Bearer',
            'expira_en' => config('jwt.ttl') * 60,
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate();

        return response()->json([
            'mensaje' => 'Sesion cerrada correctamente',
        ]);
    }
}