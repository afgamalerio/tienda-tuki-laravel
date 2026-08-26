<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function encabezadosAutenticados(?User $usuario = null): array
    {
        $usuario ??= User::factory()->create();

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');

        return [
            'Authorization' => 'Bearer '.$guard->login($usuario),
        ];
    }
}
