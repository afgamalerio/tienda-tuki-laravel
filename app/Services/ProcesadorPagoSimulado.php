<?php

namespace App\Services;

use App\Contracts\ProcesadorPago;
use App\Models\Pedido;

class ProcesadorPagoSimulado implements ProcesadorPago
{
    public function cobrar(Pedido $pedido): void
    {
        // Reemplazar con la integración real cuando exista un proveedor de pagos.
    }
}