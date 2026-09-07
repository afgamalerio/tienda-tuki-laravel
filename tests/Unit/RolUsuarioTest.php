<?php

namespace Tests\Unit;

use App\Enums\RolUsuario;
use PHPUnit\Framework\TestCase;

class RolUsuarioTest extends TestCase
{
    public function test_roles_have_stable_persistence_values(): void
    {
        self::assertSame('admin', RolUsuario::ADMIN->value);
        self::assertSame('cliente', RolUsuario::CLIENTE->value);
    }
}
