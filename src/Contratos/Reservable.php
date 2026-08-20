<?php

declare(strict_types=1);

namespace App\Contratos;

interface Reservable
{
    public function estaDisponible(string $inicio, int $horas): bool;

    public function calcularCosto(int $horas, bool $horarioPico = false): float;
}
