<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_can_run_more_than_once_without_duplicates(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

        $this->assertSame(1, User::where('email', 'test@example.com')->count());
        $this->assertSame(4, Categoria::count());
        $this->assertSame(4, Producto::count());
    }
}