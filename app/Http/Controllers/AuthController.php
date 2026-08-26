<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = auth('api')->login($usuario);

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
        $token = auth('api')->attempt($credenciales);

        if (!$token) {
            return response()->json([
                'mensaje' => 'Las credenciales son incorrectas',
            ], 401);
        }

        return response()->json([
            'mensaje' => 'Login realizado correctamente',
            'usuario' => auth('api')->user()->only(['id', 'name', 'email']),
            'token' => $token,
            'tipo_token' => 'Bearer',
            'expira_en' => config('jwt.ttl') * 60,
        ]);
    }
}