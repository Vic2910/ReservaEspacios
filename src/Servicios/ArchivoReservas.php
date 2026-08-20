<?php

declare(strict_types=1);

namespace App\Servicios;

use App\Contratos\Exportable;

final class ArchivoReservas
{
    public function __construct(private readonly string $ruta)
    {
    }

    /** @param list<Exportable> $reservas */
    public function guardar(array $reservas): void
    {
        $datos = array_map(
            static fn (Exportable $reserva): array => $reserva->aArray(),
            $reservas
        );

        file_put_contents(
            $this->ruta,
            json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
    }

    public function leer(): array
    {
        if (!is_file($this->ruta)) {
            return [];
        }

        return json_decode(file_get_contents($this->ruta), true, 512, JSON_THROW_ON_ERROR);
    }
}
