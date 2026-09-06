<?php

namespace App\Contracts;

use App\Models\Pedido;

interface ProcesadorPago
{
    public function cobrar(Pedido $pedido): void;
}