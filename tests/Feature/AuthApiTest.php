<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_and_receive_a_jwt(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'password' => 'password-segura',
            'password_confirmation' => 'password-segura',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('mensaje', 'Usuario registrado correctamente')
            ->assertJsonPath('usuario.email', 'ana@example.com')
            ->assertJsonMissingPath('usuario.password');

        $token = $response->json('token');

        $this->assertIsString($token);
        $this->assertCount(3, explode('.', $token));
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
        $this->assertTrue(Hash::check('password-segura', User::first()->password));
    }

    public function test_cannot_register_with_invalid_data(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'no-es-un-email',
            'password' => 'corta',
            'password_confirmation' => 'diferente',
        ])
            ->assertStatus(422)
            ->assertJsonPath('mensaje', 'Error de validación')
            ->assertJsonStructure(['errores' => ['name', 'email', 'password']]);
    }

    public function test_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'password-segura',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'password-segura',
        ])
            ->assertOk()
            ->assertJsonPath('mensaje', 'Login realizado correctamente')
            ->assertJsonPath('usuario.email', 'ana@example.com')
            ->assertJsonMissingPath('usuario.password');
    }

    public function test_login_returns_unauthorized_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'password-segura',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'incorrecta',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('mensaje', 'Las credenciales son incorrectas');
    }

    public function test_can_get_the_authenticated_user(): void
    {
        $usuario = User::factory()->create([
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
        ]);

        $this->getJson('/api/user', $this->encabezadosAutenticados($usuario))
            ->assertOk()
            ->assertJsonPath('usuario.email', 'ana@example.com')
            ->assertJsonMissingPath('usuario.password');
    }

    public function test_can_refresh_a_valid_token(): void
    {
        $encabezados = $this->encabezadosAutenticados();

        $this->postJson('/api/v1/auth/refresh', [], $encabezados)
            ->assertOk()
            ->assertJsonPath('mensaje', 'Token renovado correctamente')
            ->assertJsonPath('tipo_token', 'Bearer')
            ->assertJsonStructure(['token', 'expira_en']);
    }

    public function test_logout_invalidates_the_current_token(): void
    {
        $encabezados = $this->encabezadosAutenticados();

        $this->postJson('/api/v1/auth/logout', [], $encabezados)
            ->assertOk()
            ->assertJsonPath('mensaje', 'Sesion cerrada correctamente');

        $this->getJson('/api/v1/auth/me', $encabezados)
            ->assertUnauthorized()
            ->assertJsonPath('mensaje', 'El token no existe, es inválido o expiró.');
    }
}