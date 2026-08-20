<?php

declare(strict_types=1);

namespace App\Entidades;

use App\Contratos\Exportable;
use App\Espacios\Espacio;
use InvalidArgumentException;

final class Reserva implements Exportable
{
    public readonly string $id;
    public readonly float $costo;

    public function __construct(
        string $id,
        public readonly string $cliente,
        public readonly Espacio $espacio,
        public readonly string $inicio,
        public readonly int $horas,
        bool $horarioPico = false
    ) {
        if ($cliente === '') {
            throw new InvalidArgumentException('El cliente es obligatorio.');
        }

        if ($horas < 1 || $horas > 8) {
            throw new InvalidArgumentException('La reserva debe durar entre 1 y 8 horas.');
        }

        if (!$espacio->estaDisponible($inicio, $horas)) {
            throw new InvalidArgumentException('El espacio ya esta ocupado en ese horario.');
        }

        $this->id = $id;
        $this->costo = $espacio->calcularCosto($horas, $horarioPico);
        $espacio->reservar($inicio, $horas);
    }

    public function aArray(): array
    {
        return [
            'id' => $this->id,
            'cliente' => $this->cliente,
            'espacio' => $this->espacio->codigo,
            'inicio' => $this->inicio,
            'horas' => $this->horas,
            'costo' => $this->costo,
        ];
    }
}
