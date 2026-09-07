<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

abstract class TestCase extends BaseTestCase
{
    protected function encabezadosAutenticados(?User $usuario = null): array
    {
        $usuario ??= User::factory()->create();

        /** @var JWTGuard $guard */
        $guard = auth('api');

        return [
            'Authorization' => 'Bearer '.$guard->login($usuario),
        ];
    }

    protected function encabezadosAdmin(): array
    {
        return $this->encabezadosAutenticados(
            User::factory()->create(['rol' => 'admin'])
        );
    }
}
