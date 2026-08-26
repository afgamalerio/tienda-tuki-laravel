<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function encabezadosAutenticados(?User $usuario = null): array
    {
        $usuario ??= User::factory()->create();

        return [
            'Authorization' => 'Bearer '.auth('api')->login($usuario),
        ];
    }
}
