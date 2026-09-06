<?php

namespace App\Providers;

use App\Contracts\ProcesadorPago;
use App\Services\ProcesadorPagoSimulado;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProcesadorPago::class, ProcesadorPagoSimulado::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
