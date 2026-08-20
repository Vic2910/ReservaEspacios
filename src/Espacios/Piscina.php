<?php

declare(strict_types=1);

namespace App\Espacios;

final class Piscina extends Espacio
{
    public function calcularCosto(int $horas, bool $horarioPico = false): float
    {
        $recargo = $horarioPico ? 1.10 : 1.0;
        return $this->tarifaBase * $horas * $recargo;
    }
}
