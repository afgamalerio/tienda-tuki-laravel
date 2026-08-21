<?php

namespace App\Data;

class CheckoutData
{
    public function __construct(
        public readonly string $nombreDestinatario,
        public readonly string $direccion,
        public readonly string $ciudad,
        public readonly string $metodoPago,
    ) {
    }

    public static function fromArray(array $datos): self
    {
        return new self(
            $datos['nombre_destinatario'],
            $datos['direccion'],
            $datos['ciudad'],
            $datos['metodo_pago'],
        );
    }

    public function toArray(): array
    {
        return [
            'nombre_destinatario' => $this->nombreDestinatario,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'metodo_pago' => $this->metodoPago,
        ];
    }
}
