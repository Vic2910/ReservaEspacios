<?php

declare(strict_types=1);

namespace App\Espacios;

final class CanchaFutbol extends Espacio
{
    public function __construct(string $codigo, string $nombre, float $tarifaBase)
    {
        parent::__construct($codigo, $nombre, $tarifaBase);
    }

    public function calcularCosto(int $horas, bool $horarioPico = false): float
    {
        $recargo = $horarioPico ? 1.25 : 1.0;
        return $this->tarifaBase * $horas * $recargo;
    }
}
