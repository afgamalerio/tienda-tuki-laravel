<?php

namespace App\Exceptions;

use RuntimeException;

class StockInsuficienteException extends RuntimeException
{
    public function __construct(
        public readonly string $producto,
        public readonly int $disponible,
        public readonly int $solicitado,
    ) {
        parent::__construct('No hay stock suficiente para el producto.');
    }
}
